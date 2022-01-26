<?php

/**
 * @method static self UP()
 * @method static self LEFT()
 * @method static self RIGHT()
 * @method static self DOWN()
 */
class Direction
{
    private string $value;

    private const UP = 'UP';
    private const LEFT = 'LEFT';
    private const RIGHT = 'RIGHT';
    private const DOWN = 'DOWN';

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function __callStatic(string $name, array $arguments): static
    {
        return new static($name);
    }

    public function equal(Direction $direction): bool
    {
        return $this->value === $direction->value;
    }

    public function reverse(): self
    {
        return match ($this->value) {
            self::DOWN => new self(self::UP),
            self::UP => new self(self::DOWN),
            self::LEFT => new self(self::RIGHT),
            self::RIGHT => new self(self::LEFT),
        };
    }

    public function next(): self
    {
        return match ($this->value) {
            self::UP => new self(self::LEFT),
            self::LEFT => new self(self::RIGHT),
            self::RIGHT => new self(self::DOWN),
            self::DOWN => new self(self::UP),
        };
    }
}
