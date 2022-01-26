<?php

declare(strict_types=1);

$wins = [
    'player1' => ['conf' => '[bias p: none, bias a: none]', 'win' => 0, 'turn' => 0],
    'player2' => ['conf' => '[bias p: none, bias a: none]', 'win' => 0, 'turn' => 0],
];

$nbGames = 100000;
for ($i = 0; $i < $nbGames; $i++) {
    $game = new Game(
        new Player('player1', 'battle.php', true),
        new Player('player2', 'battle_ai_bad.php', true)
    );

    $count = 0;
    while ($count++ < 200) {
        if ($game->turn() === 'won') {
            break;
        }
    }

    $game->stop('end' . ' ' . $i);

    if ($count === 200) {
        echo "unresolved game after 100 turns\n";
    }

    $winner = $game->getCurrent()->getName() === 'player1' ? 'player2' : 'player1';
    $wins[$winner]['win']++;
    $wins[$winner]['turn'] += ceil($count / 2);
}

foreach ($wins as $player => $data) {
    echo $player . ':' . PHP_EOL;
    echo ' . conf: ' . $data['conf'] . PHP_EOL;
    echo ' . wins: ' . $data['win'] . PHP_EOL;
    echo ' . turn: ' . ($data['turn'] / $data['win']) . PHP_EOL;
}

class Game {
    private int $currentPlayer;
    /** @var Player[] */
    private array $players;

    public function __construct(Player $p1, Player $p2)
    {
        $this->players = [
            0 => $p1,
            1 => $p2,
        ];
        $this->currentPlayer = \mt_rand(0,1);
    }

    public function turn(): string
    {
        try {
            $coord = $this->getCurrent()->request('your turn');
            $result = $this->getOpponent()->request($coord);
            $this->throwIfError($result, $this->getOpponent()->getName());
            $react = $this->getCurrent()->request($result);
            $this->throwIfError($react, $this->getCurrent()->getName());

            $this->currentPlayer = $this->currentPlayer === 0 ? 1 : 0;

            return $result;
        } catch (\Throwable $exception) {
            $this->stop($exception->getMessage());
            exit;
        }
    }

    public function stop(string $why): void
    {
        echo $why, "\r";
        $this->players[0]->stop();
        $this->players[1]->stop();
    }

    public function getCurrent(): Player
    {
        return $this->players[$this->currentPlayer];
    }

    private function getOpponent(): Player
    {
        return $this->players[$this->currentPlayer === 0 ? 1 : 0];
    }

    private function throwIfError(string $response, string $name): void
    {
        //if (\str_starts_with($response, 'error')) {
        if (strpos($response, 'error') === 0) {
            throw new RuntimeException("{$name} said: {$response}");
        }
    }
}

class Player {
    private const TIMEOUT = 1;

    private bool $debug;
    private string $name;
    /** @var resource */
    private $process;
    /** @var resource[]  */
    private array $pipes = [];

    public function __construct(string $name, string $script, bool $debug = false)
    {
        $allowedScript = [
            'battle.php',
            'battle_ai_random.php',
            'battle_ai_mad.php',
            'battle_ai_bad.php',
        ];
        $baseName = basename($script);
        if (!in_array($baseName, $allowedScript)) {
            throw new RuntimeException('battle script must be base named "' . implode('" or "', $allowedScript) . '" (given: ' . $baseName . ')');
        }
        $script = realpath($script);
        if ($script === false) {
            throw new RuntimeException('Could not found script');
        }

        $cwd = dirname($script);
        $process = proc_open(
            'php '.$script,
            [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                2 => ["pipe", "w"],
            ],
            $this->pipes,
            $cwd
        );

        if ($process === false) {
            throw new RuntimeException('Could not start process php '.$script);
        }

        $this->process = $process;

        stream_set_blocking($this->pipes[1], false);
        stream_set_blocking($this->pipes[2], false);
        $this->name = $name;
        $this->debug = $debug;
    }

    public function request(string $command): string
    {
        $meta = proc_get_status($this->process);
        if ($meta['running'] === false) {
            $error = stream_get_contents($this->pipes[2]);
            if ($error !== '') {
                $error = ': '.$error;
            }
            throw new RuntimeException("Process {$this->name} not running".$error);
        }
        if ($this->debug) {
            echo "{$this->name} < {$command}\n";
        }
        fwrite($this->pipes[0], "{$command}\n");

        $line = '';
        $start = microtime(true);
        while ($line === '' || $line[-1] !== "\n") {
            $gets = fgets($this->pipes[1]);
            if (false === $gets) {
                if (microtime(true) - $start > self::TIMEOUT) {
                    throw new RuntimeException('timeout');
                }
                continue;
            }
            $line .= $gets;
        }

        $line = trim($line);
        if ($this->debug) {
            echo "{$this->name} > {$line}\n";
        }

        return $line;
    }

    public function stop(): void
    {
        fclose($this->pipes[0]);
        fclose($this->pipes[1]);
        fclose($this->pipes[2]);
        proc_close($this->process);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
