<?php

declare(strict_types=1);

namespace Challenge;

final class Coordinate
{
    private string $coordinate;

    public function __construct(string $coordinate)
    {
        $this->coordinate = $coordinate;
    }

    public function __toString(): string
    {
        return $this->coordinate;
    }

    public function getY(): int
    {
        return ord($this->coordinate[0]);
    }

    public function getX(): int
    {
        return (int)substr($this->coordinate, 1, 2);
    }

    public function moveTop(): void
    {
        $this->coordinate = chr($this->getY()-1) . $this->getX();
    }

    public function moveTopSafe(): void
    {
        $this->coordinate = chr(max($this->getY()-1, 65)) . $this->getX();
    }

    public function moveRight(int $number = 1): void
    {
        $this->coordinate = chr($this->getY()) . $this->getX() + $number . "";
    }

    public function moveRightSafe(): void
    {
        $this->coordinate = chr($this->getY()) . min($this->getX() + 1, 10);
    }

    public function moveBottom(int $number = 1): void
    {
        $this->coordinate = chr($this->getY()+$number) . $this->getX();
    }

    public function moveBottomSafe(int $number = 1): void
    {
        $this->coordinate = chr(min($this->getY()+$number, 74)) . $this->getX();
    }

    public function moveLeft(): void
    {
        $this->coordinate = chr($this->getY()) . $this->getX() - 1 . "";
    }

    public function moveLeftSafe(): void
    {
        $this->coordinate = chr($this->getY()) . max($this->getX() - 1, 1);
    }

    public function isValid(): bool
    {
        return (bool)preg_match('`^([A-J](?:[1-9]|10))$`i', $this->coordinate);
    }
}
