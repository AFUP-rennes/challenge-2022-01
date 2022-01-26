<?php
declare(strict_types=1);

namespace Challenge;
final class Challenge
{
    private array $players;
    private array $winners;
    private array $errors;

    public function __construct(private int $nbIterations, private int $winLevel, private bool $debug)
    {
        $this->players = [];
        foreach (glob(__DIR__.'/../players/*') as $file) {
            if (basename($file)[0] === '_') {
                echo 'skip ', $file, "\n";
                continue;
            }
            if (file_exists($file . '/battle.php')) {
                $this->players[] = $file . '/battle.php';
            }
        }
        $this->errors = [];
    }

    public function start(callable $progression): void
    {
        $this->winners = [];

        $len = count($this->players);

        $progressLen = (($len * $len) - $len) / 2 * $this->nbIterations;
        echo "\nWill play {$progressLen} games\n";
        $progressCount = 0;

        for ($i = 0; $i < $len; $i++) {
            for ($j = $i + 1; $j < $len; $j++) {
                $player1 = $this->players[$i];
                $player2 = $this->players[$j];

                $name1 = \basename(\dirname($player1));
                $name2 = \basename(\dirname($player2));

                $this->winners[$name1] ??= [
                    'won' => 0,
                    'timeSum' => 0,
                    'count' => 0,
                ];
                $this->winners[$name2] ??= [
                    'won' => 0,
                    'timeSum' => 0,
                    'count' => 0,
                ];

                $count = $this->nbIterations;
                $countWinThisGame = [
                    $name1 => 0,
                    $name2 => 0,
                ];
                while ($count--) {
                    $progression($name1, $name2, (int) round($progressCount/$progressLen*100));
                    $game = new Game(
                        new Player($name1, $player1, $this->debug),
                        new Player($name2, $player2, $this->debug)
                    );

                    $stats = $game->play();
                    $this->winners[$name1]['timeSum'] += $stats[$name1];
                    $this->winners[$name1]['count']++;
                    $this->winners[$name2]['timeSum'] += $stats[$name2];
                    $this->winners[$name2]['count']++;
                    if (isset($stats['winner'])) {
                        $countWinThisGame[$name1] += ($stats['winner'] === $name1) ? 1 : 0;
                        $countWinThisGame[$name2] += ($stats['winner'] === $name2) ? 1 : 0;
                    } elseif (isset($stats['error'])) {
                        $this->errors[$stats['error']] = $stats['error'];
                        if (strpos($stats['error'], 'timeout') !== false) {
                            break;
                        }
                    }
                    $progressCount++;
                }

                if ($countWinThisGame[$name1] >= $this->winLevel) {
                    $this->winners[$name1]['won']++;
                }
                if ($countWinThisGame[$name2] >= $this->winLevel) {
                    $this->winners[$name2]['won']++;
                }
            }
        }
    }

    public function getResult(): array
    {
        uasort($this->winners, function ($a, $b) {
            if ($b['won'] !== $a['won']) {
                return $b['won'] <=> $a['won'];
            }

            if ($a['count'] === 0 || $b['count'] === 0) {
                return -1;
            }
            return ($a['timeSum'] / $a['count']) <=> ($b['timeSum'] / $b['count']);
        });

        $result = [];
        $position = 0;
        foreach ($this->winners as $name => $winner) {
            $position++;

            $result[$position] = [
                'name' => $name,
                'won' => $winner['won'],
                'perf' => $winner['count'] > 0 ? round($winner['timeSum'] / $winner['count'] * 1000000) : 1000000,
                'errors' => $this->errors,
            ];
        }

        return $result;
    }

}