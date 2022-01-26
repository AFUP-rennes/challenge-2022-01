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

interface ShipUnknownInterface
{
    public function discover(Coordinates $part): self;

    public function convert(bool $alreadyHaveCruiser = false): ShipInterface;
}
