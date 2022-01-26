<?php
declare(strict_types=1);

class Boat
{
    /** @var array<string> $cells  */
    private array $cells = [];
    public function __construct(
        private int $size
    ){}

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    public function setCells(array $cells): self
    {
        $this->cells = $cells;
        return $this;
    }

    public function isOnCell(string $coordinates): bool
    {
        return in_array($coordinates, $this->cells);
    }

    public function hitCell(string $coordinates): int {
        $this->cells = array_diff($this->cells,[$coordinates]);
        return count($this->cells);
    }

    public function isSunk(): bool
    {
        return count($this->cells) === 0;
    }
}