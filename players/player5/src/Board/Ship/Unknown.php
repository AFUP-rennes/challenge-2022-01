<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Board\Ship;

use Application\Board\Coordinates;
use Application\Exception\InvalidShipSizeException;

class Unknown extends AbstractShip implements ShipInterface, ShipUnknownInterface
{
    /** @var Coordinates[] $parts */
    private array $parts = [];

    public function getSize(): int
    {
        return count($this->parts); // Get current size
    }

    public function discover(Coordinates $part): self
    {
        $this->parts[(string) $part] = $part;

        ksort($this->parts, SORT_NATURAL);

        if ($this->getSize() > 5) {
            throw new InvalidShipSizeException('Invalid ship size (size: ' . $this->getSize() . ')');
        }

        return $this;
    }

    public function convert(bool $alreadyHaveCruiser = false): ShipInterface
    {
        switch ($this->getSize()) {
            case 5:
                $ship = new Carrier();
                break;
            case 4:
                $ship = new Battleship();
                break;
            case 3:
                $ship = $alreadyHaveCruiser ? new Submarine() : new Cruiser();
                break;
            case 2:
                $ship = new Destroyer();
                break;
            default:
                throw new InvalidShipSizeException('Invalid ship size (size: ' . $this->getSize() . ')');
        }

        $origin      = reset($this->parts);
        $orientation = $this->calculateOrientation();

        $ship->setOrigin($origin, $orientation);

        return $ship;
    }

    private function calculateOrientation(): int
    {
        $first  = reset($this->parts);
        $second = next($this->parts);

        return $first->getColumn() === $second->getColumn() ? ShipInterface::VERTICAL : ShipInterface::HORIZONTAL;
    }
}
