<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\AI\Placer;

use Application\Board\Coordinates;
use Application\Board\Ship;

class InvalidAIPlacer implements AIPlacerInterface
{
    public function place(): array
    {
        return [
            (new Ship\Carrier())->setOrigin(new Coordinates(1, 1), Ship\ShipInterface::HORIZONTAL),
            (new Ship\Battleship())->setOrigin(new Coordinates(1, 3), Ship\ShipInterface::VERTICAL),
            (new Ship\Cruiser())->setOrigin(new Coordinates(10, 6), Ship\ShipInterface::VERTICAL),
            (new Ship\Destroyer())->setOrigin(new Coordinates(5, 8), Ship\ShipInterface::HORIZONTAL),
            (new Ship\Destroyer())->setOrigin(new Coordinates(6, 7), Ship\ShipInterface::VERTICAL),
            (new Ship\Destroyer())->setOrigin(new Coordinates(5, 10), Ship\ShipInterface::HORIZONTAL),
        ];
    }
}
