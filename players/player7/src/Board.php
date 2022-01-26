<?php
declare(strict_types=1);

use Assert\Assertion;
use Assert\AssertionFailedException;

abstract class Board
{
    /** @var array<Cell> $container */
    protected array $container = [];

    public function __construct()
    {
        foreach (range(0, 9) as $column) {
            foreach (range(0, 9) as $line) {
                $this->container["$column$line"] = new Cell($column, $line);
            }
        }
    }

    public function keyOrNull($key): ?string
    {
        if ($this->isInTheBoard($key) && $this->isUnknown("$key")){
            return $key;
        }
        return null;
    }

    public function isInTheBoard(string $value): bool
    {
        try {
            Assertion::length($value, 2);
            Assertion::range((int)$value, 0, 99);
        } catch (AssertionFailedException) {
            return false;
        }
        return isset($this->container[str_pad("$value", 2, "0", STR_PAD_LEFT)]);
    }

    public function isUnknown(string $key): bool
    {
        return $this->container[$key]->getStatus()->equals(CELL_STATUS::Unknown());
    }

    public function isFull(string $key): bool
    {
        return $this->container[$key]->getStatus()->equals(CELL_STATUS::Full());
    }
}