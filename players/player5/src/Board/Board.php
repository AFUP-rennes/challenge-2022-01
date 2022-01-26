<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Board;

use Application\Board\Ship\ShipInterface;

final class Board
{
    /** @var int[] $board */
    private array $board;

    public function __construct(int $defaultState)
    {
        $this->board = [];

        for ($line = 1; $line <= 10; $line++) {
            for ($column = 1; $column <= 10; $column++) {
                $this->board[(string) new Coordinates($column, $line)] = $defaultState;
            }
        }
    }

    public function addShip(ShipInterface $ship): void
    {
        foreach ($ship->get() as $coordinates) {
            $this->ship($coordinates);
        }
    }

    public function canPlaceShip(ShipInterface $ship): bool
    {
        $orientation = $ship->getOrientation();
        $isStart     = true;

        foreach ($ship->get() as $position) {

            if (!isset($this->board[(string) $position])) {
                return false;
            }

            if ($isStart) {
                $around = [
                    $position->getTopLeft(),
                    $position->getTop(),
                    $position->getTopRight(),
                    $position->getRight(),
                    $position->getBottomRight(),
                    $position->getBottom(),
                    $position->getBottomLeft(),
                    $position->getLeft(),
                ];
            } elseif ($orientation === ShipInterface::VERTICAL) {
                $around = [
                    $position->getBottomRight(),
                    $position->getBottom(),
                    $position->getBottomLeft(),
                ];
            } else {
                $around = [
                    $position->getTopRight(),
                    $position->getRight(),
                    $position->getBottomRight(),
                ];
            }

            foreach ($around as $coordinates) {
                $state = $this->board[(string) $coordinates] ?? null;
                if ($state === State::SHIP) {
                    return false;
                }
            }

            $isStart = false;
        }

        return true;
    }

    public function exists(Coordinates $coordinates): bool
    {
        return isset($this->board[(string) $coordinates]);
    }

    public function getState(Coordinates $coordinates): int
    {
        return $this->board[(string) $coordinates];
    }

    public function ship(Coordinates $coordinates): void
    {
        $this->board[(string) $coordinates] = State::SHIP;
    }

    public function check(Coordinates $coordinates): void
    {
        $this->board[(string) $coordinates] |= State::CHECKED;
        $this->notUnknown($coordinates);
    }

    public function sunk(Coordinates $coordinates): void
    {
        $this->ship($coordinates);
        $this->board[(string) $coordinates] |= State::SUNK;
    }

    public function hit(Coordinates $coordinates): void
    {
        $this->ship($coordinates);
        $this->board[(string) $coordinates] |= State::HIT;
    }

    public function miss(Coordinates $coordinates): void
    {
        $this->board[(string) $coordinates] |= (State::MISS | State::EMPTY);
        $this->notUnknown($coordinates);
    }

    public function empty(Coordinates $coordinates): void
    {
        $this->board[(string) $coordinates] |= State::EMPTY;
        $this->notUnknown($coordinates);
    }

    public function notUnknown(Coordinates $coordinates): void
    {
        $this->board[(string) $coordinates] = ($this->board[(string) $coordinates] & ~State::UNKNOWN);
    }

    public function __toString(): string
    {
        $board = "Board:\n------------" . PHP_EOL;
        for ($line = 1; $line <= 10; $line++) {
            $board .= '|';
            for ($column = 1; $column <= 10; $column++) {
                $state = $this->getState(new Coordinates($column, $line));
                switch (true) {
                    case ($state & State::SHIP) === State::SHIP:
                        $cell = 'o';
                        break;
                    case ($state & State::EMPTY) === State::EMPTY:
                        $cell = '~';
                        break;
                    case ($state & State::UNKNOWN) === State::UNKNOWN:
                    default:
                        $cell = '.';
                        break;
                }
                $board .= $cell;
            }
            $board .= '|' . PHP_EOL;
        }
        $board .= '------------' . PHP_EOL;

        return $board;
    }
}
