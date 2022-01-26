<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Board\Ship;

class Destroyer extends AbstractShip implements ShipInterface
{
    public function getSize(): int
    {
        return 2;
    }
}
