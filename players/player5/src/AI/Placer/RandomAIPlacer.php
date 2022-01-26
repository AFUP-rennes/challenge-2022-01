<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\AI\Placer;

use Application\AI\Bias;
use Application\Board\Board;
use Application\Board\Coordinates;
use Application\Board\Ship;
use Application\Board\State;
use Application\Exception\InvalidPlacementShipException;

class RandomAIPlacer implements AIPlacerInterface
{
    private bool $onlyAtBorders;
    private int $biasType;

    public function __construct(bool $onlyAtBorders, int $biasType)
    {
        $this->onlyAtBorders = $onlyAtBorders;
        $this->biasType      = $biasType;
    }

    public function place(): array
    {
        /** @var Ship\ShipInterface[] $shipToPlace */
        $shipToPlace = [
            new Ship\Carrier(),
            new Ship\Battleship(),
            new Ship\Cruiser(),
            new Ship\Submarine(),
            new Ship\Destroyer(),
        ];

        for ($try = 1; $try <= 3; $try++) {
            $ships = [];
            $board = new Board(State::EMPTY);
            foreach ($shipToPlace as $ship) {
                try {
                    $this->placeShipAtRandom($board, $ship);
                } catch (InvalidPlacementShipException $exception) {
                    //~ Cannot place a ship, so reset all ship - Security, but no case detected on 10k boards generation
                    continue 2;
                }

                $board->addShip($ship);
                $ships[] = $ship;
            }
            break;
        }

        return $ships;
    }

    private function placeShipAtRandom(Board $board, Ship\ShipInterface $ship): void
    {
        foreach ($this->getPossibleOrigins() as $start) {
            foreach ($this->getPossibleOrientations($start) as $orientation) {
                $ship->setOrigin($start, $orientation);

                if ($board->canPlaceShip($ship)) {
                    return;
                }
            }
        }

        throw new InvalidPlacementShipException();
    }

    /**
     * @return Coordinates[]
     */
    private function getPossibleOrigins(): array
    {
        $origins = [];

        for ($column = 1; $column <= 10; $column++) {
            for ($line = 1; $line <= 10; $line++) {
                $isBiasedCell = $this->biasType !== Bias::NONE && ($column + $line) % 2 === $this->biasType;
                if ($this->biasType !== Bias::NONE && !$isBiasedCell) {
                    continue;
                }

                $isNotBorderCell = $column > 1 && $column < 10 && $line > 1 && $line < 10;
                if ($this->onlyAtBorders && $isNotBorderCell) {
                    continue;
                }

                $coordinates = new Coordinates($column, $line);
                $origins[(string) $coordinates] = $coordinates;
            }
        }

        shuffle($origins);

        return $origins;
    }

    private function getPossibleOrientations(Coordinates $start): array
    {
        $orientations = [];
        if (!$this->onlyAtBorders || in_array($start->getColumn(), [1, 10])) {
            $orientations[] = Ship\ShipInterface::VERTICAL;
        }
        if (!$this->onlyAtBorders || in_array($start->getLine(), [1, 10])) {
            $orientations[] = Ship\ShipInterface::HORIZONTAL;
        }

        shuffle($orientations);

        return $orientations;
    }
}
