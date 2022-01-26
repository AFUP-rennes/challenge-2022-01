<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Board;

final class Coordinates
{
    private int $column;
    private int $line;

    public static function fromString(string $string): self
    {
        return new Coordinates(ord($string[0]) - 64, (int) substr($string, 1));
    }

    public function __construct(int $column, int $line)
    {
        $this->column = $column;
        $this->line   = $line;
    }

    public function __toString(): string
    {
        return chr($this->column + 64) . $this->line;
    }

    public function getColumn(): int
    {
        return $this->column;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getTopRight(): self
    {
        return new Coordinates($this->column + 1, $this->line - 1);
    }

    public function getTopLeft(): self
    {
        return new Coordinates($this->column - 1, $this->line - 1);
    }

    public function getBottomRight(): self
    {
        return new Coordinates($this->column + 1, $this->line + 1);
    }

    public function getBottomLeft(): self
    {
        return new Coordinates($this->column - 1, $this->line + 1);
    }

    public function getTop(): self
    {
        return new Coordinates($this->column, $this->line - 1);
    }

    public function getBottom(): self
    {
        return new Coordinates($this->column, $this->line + 1);
    }

    public function getLeft(): self
    {
        return new Coordinates($this->column - 1, $this->line);
    }

    public function getRight(): self
    {
        return new Coordinates($this->column + 1, $this->line);
    }
}
