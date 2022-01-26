<?php

declare(strict_types=1);

namespace App;

use App\Exception\ShipOutOfBoundException;
use App\Exception\ShipOverlapsAnotherException;

final class Fleet
{
    /**
     * @var Ship[]
     */
    private array $fleet = [];

    public function __construct(private int $gridSize)
    {
    }

    /**
     * @throws ShipOverlapsAnotherException
     * @throws ShipOutOfBoundException
     */
    public function addShip(Ship $ship): void
    {
        foreach ($ship->getCoordinates() as $coordinate) {
            if ($coordinate->fitInGrid($this->gridSize) === false) {
                throw new ShipOutOfBoundException('Ship goes beyond grid limit');
            }
        }

        foreach ($this->fleet as $placedShip) {
            if ($ship->isClosedTo($placedShip)) {
                throw new ShipOverlapsAnotherException('Ship overlaps another one');
            }
        }
        $this->fleet[] = $ship;
    }

    public function getShipAt(Coordinate $coordinate): ?Ship
    {
        foreach ($this->fleet as $ship) {
            if ($ship->isHitBy($coordinate)) {
                return $ship;
            }
        }

        return null;
    }

    public function hasFleetBeenSunk(): bool
    {
        foreach ($this->fleet as $ship) {
            if ($ship->isSunk() === false) {
                return false;
            }
        }

        return true;
    }
}
