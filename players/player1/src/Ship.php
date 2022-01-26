<?php

declare(strict_types=1);

namespace App;

final class Ship
{
    public const ORIENTATION_HORIZONTAL = 0;
    public const ORIENTATION_VERTICAL = 1;

    /** @var Coordinate[] */
    private array $coordinates;
    /** @var bool[] */
    private array $hits;
    /** @var bool */
    private bool $sunk;

    public function __construct(Coordinate $coordinate, int $size, int $orientation)
    {
        $this->coordinates = [];
        $this->hits = [];
        $this->sunk = false;
        for ($i = 0; $i < $size; $i++) {
            $this->coordinates[$i] = $coordinate;
            $this->hits[$i] = false;
            if ($orientation === self::ORIENTATION_HORIZONTAL) {
                $coordinate = $coordinate->right();
            } else {
                $coordinate = $coordinate->bottom();
            }
        }
    }

    public function damage(Coordinate $damageCoordinate): bool
    {
        foreach ($this->coordinates as $i => $coordinate) {
            if ($damageCoordinate->equals($coordinate)) {
                $this->hits[$i] = true;
                break;
            }
        }

        foreach ($this->hits as $hit) {
            if ($hit === false) {
                return false;
            }
        }

        $this->sunk = true;

        return true;
    }

    /**
     * @return Coordinate[]
     */
    public function getCoordinates(): array
    {
        return $this->coordinates;
    }

    public function isClosedTo(Ship $other): bool
    {
        foreach ($this->coordinates as $coordinate) {
            foreach ($other->coordinates as $otherCoordinate) {
                if ($coordinate->isClosedTo($otherCoordinate)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isHitBy(Coordinate $hitCoordinate): bool
    {
        foreach ($this->coordinates as $coordinate) {
            if ($hitCoordinate->equals($coordinate)) {
                return true;
            }
        }

        return false;
    }

    public function isSunk(): bool
    {
        return $this->sunk;
    }
}
