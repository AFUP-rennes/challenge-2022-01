<?php

/**
 * @method static self unknown()
 * @method static self untarget()
 * @method static self miss()
 * @method static self hit()
 * @method static self sunk()
 * @method static self won()
 * @method static self error()
 */
class GridIndexKnowledge
{
    private string $value;

    private const unknown = 'unknown';
    private const untarget = 'untarget';
    private const miss = 'miss';
    private const hit = 'hit';
    private const sunk = 'sunk';
    private const won = 'won';
    private const error = 'error';

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function __callStatic(string $name, array $arguments): static
    {
        return new static($name);
    }

    public function equal(GridIndexKnowledge $gridIndexKnowledge): bool
    {
        return $this->value === $gridIndexKnowledge->value;
    }

    public function hasHit(): bool
    {
        return match ($this->value) {
            self::hit, self::sunk, self::won => true,
            default => false,
        };
    }

    public function toString(): string
    {
        return $this->value;
    }
}
