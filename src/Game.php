<?php

declare(strict_types=1);

namespace Challenge;

final class Game
{
    private int $currentPlayer;
    /** @var Player[] */
    private array $players;

    public function __construct(Player $p1, Player $p2)
    {
        $this->players = [
            0 => $p1,
            1 => $p2,
        ];
        $this->currentPlayer = \random_int(0, 1);
    }

    public function play(): array
    {
        $result = [];
        $count = 0;
        while ($count++ < 200) {
            $turn = $this->turn();
            if ($turn === 'won') {
                $result['winner'] = $this->getOpponent()->getName();
                break;
            }

            if (stripos($turn, 'error') === 0) {
                $result['error'] = $this->getCurrent()->getName().' replied to '. $this->getOpponent()->getName().': '.$turn;
                break;
            }
        }

        $this->stop();

        if ($count === 200) {
            echo "unresolved game after 100 turns\n";
        }

        $result['turns'] = $count;

        foreach ($this->players as $player) {
            $result[$player->getName()] = $player->getResponseTime();
        }

        return $result;
    }

    public function turn(): string
    {
        try {
            $coord = $this->getCurrent()->request('your turn');
            $this->throwIfError($coord, $this->getCurrent()->getName());
            $result = $this->getOpponent()->request($coord);
            $this->throwIfError($result, $this->getOpponent()->getName());
            $react = $this->getCurrent()->request($result);
            $this->throwIfError($react, $this->getCurrent()->getName());

            $this->currentPlayer = $this->currentPlayer === 0 ? 1 : 0;

            return $result;
        } catch (\Throwable $exception) {
            $this->stop();
            return 'error:'.$exception->getMessage();
        }
    }

    public function stop(): void
    {
        $this->players[0]->stop();
        $this->players[1]->stop();
    }

    private function getCurrent(): Player
    {
        return $this->players[$this->currentPlayer];
    }

    private function getOpponent(): Player
    {
        return $this->players[$this->currentPlayer === 0 ? 1 : 0];
    }

    private function throwIfError(string $response, string $name): void
    {
        if (\stripos($response, 'error') === 0) {
            throw new \RuntimeException(preg_replace('`^error\s*:?`i', '', $response));
        }
    }
}
