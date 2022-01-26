<?php

declare(strict_types=1);

namespace App;

final class Coordinate
{
    public function __construct(private int $x, private int $y)
    {
    }

    public function bottom(): self
    {
        return new self($this->x, $this->y + 1);
    }

    public function bottomLeft(): self
    {
        return new self($this->x - 1, $this->y + 1);
    }

    public function bottomRight(): self
    {
        return new self($this->x + 1, $this->y + 1);
    }

    public function equals(self $coordinate): bool
    {
        return $this->x === $coordinate->x && $this->y === $coordinate->y;
    }

    public function fitInGrid(int $size): bool
    {
        return $this->x >= 0 && $this->x < $size && $this->y >= 0 && $this->y < $size;
    }

    public static function fromString(string $string): self
    {
        $x = ord($string[0]) - ord('A');
        $y = (int) substr($string, 1) - 1;
        return new self($x, $y);
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function isClosedTo(Coordinate $coordinate): bool
    {
        return
            $coordinate->x >= $this->x - 1 && $coordinate->x <= $this->x + 1
            &&
            $coordinate->y >= $this->y - 1 && $coordinate->y <= $this->y + 1
        ;
    }

    public function left(): self
    {
        return new self($this->x - 1, $this->y);
    }

    public function right(): self
    {
        return new self($this->x + 1, $this->y);
    }

    public function sailOnGrid(int $size): self
    {
        $x = $this->x + 1;
        $y = $this->y;
        if ($x >= $size) {
            $x = 0;
            $y++;
        }

        return new Coordinate($x, $y);
    }

    public function top(): self
    {
        return new self($this->x, $this->y - 1);
    }

    public function topLeft(): self
    {
        return new self($this->x - 1, $this->y - 1);
    }

    public function topRight(): self
    {
        return new self($this->x + 1, $this->y - 1);
    }

    public function toString(): string
    {
        return chr($this->x + ord('A')) . ($this->y + 1);
    }
}
