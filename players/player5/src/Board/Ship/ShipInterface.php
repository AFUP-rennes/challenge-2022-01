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

interface ShipInterface
{
    public const VERTICAL   = 1;
    public const HORIZONTAL = 2;

    /**
     * @return Coordinates[]
     */
    public function get(): array;

    public function getOrientation(): int;

    public function getSize(): int;

    public function hit(Coordinates $coordinates): void;

    public function isSunk(): bool;

    public function setOrigin(Coordinates $start, int $orientation): self;
}
