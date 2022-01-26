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

abstract class AbstractShip implements ShipInterface
{
    /** @var Coordinates[] $ship */
    private array $ship = [];
    private array $hits  = [];
    private int $orientation = 0;

    abstract public function getSize(): int;

    public function setOrigin(Coordinates $start, int $orientation): self
    {
        $this->ship = [];
        $this->hits = [];

        for ($i = 0; $i < $this->getSize(); $i++) {
            if ($orientation === ShipInterface::HORIZONTAL) {
                $coordinates = new Coordinates($start->getColumn() + $i, $start->getLine());
            } else {
                $coordinates = new Coordinates($start->getColumn(), $start->getLine() + $i);
            }

            $this->ship[] = $coordinates;
            $this->hits[(string) $coordinates] = 0;
        }

        $this->orientation = $orientation;

        return $this;
    }

    /**
     * @return Coordinates[]
     */
    public function get(): array
    {
        return $this->ship;
    }

    public function getName(): string
    {
        return basename(strtr(get_class($this), '\\', '/'));
    }

    public function getOrientation(): int
    {
        return $this->orientation;
    }

    public function hit(Coordinates $coordinates): void
    {
        $this->hits[(string) $coordinates] = 1;
    }

    public function isSunk(): bool
    {
        return (array_sum($this->hits) === $this->getSize());
    }
}
