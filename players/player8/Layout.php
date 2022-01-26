<?php

class Layout implements IteratorAggregate
{
    public const SIZE = 10;

    /** @var array<int, GridIndexKnowledge> */
    private array $grid = [];

    public function __construct()
    {
        for ($i = 0; $i < self::SIZE ** 2; $i++) {
            $this->grid[$i] = GridIndexKnowledge::unknown();
        }
    }

    /**
     * @return array<GridIndexKnowledge>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->grid);
    }

    public function getGridIndex(int $index): GridIndexKnowledge
    {
        return $this->grid[$index];
    }

    public function setGridIndex(int $index, GridIndexKnowledge $gridIndexKnowledge): void
    {
        $this->grid[$index] = $gridIndexKnowledge;
    }

    public function move(int $index, Direction $direction): ?int
    {
        return match (true) {
            $direction->equal(Direction::UP()) => ($index >= self::SIZE) ? $index - self::SIZE : null,
            $direction->equal(Direction::LEFT()) => ($index % self::SIZE !== 0) ? $index - 1 : null,
            $direction->equal(Direction::RIGHT()) => (($index + 1) % self::SIZE !== 0) ? $index + 1 : null,
            $direction->equal(Direction::DOWN()) => (($index + self::SIZE) < self::SIZE ** 2) ? $index + self::SIZE : null,
        };
    }

    /**
     * @return array<int, int>
     */
    public function getValidIndexesCloseTo(int $index): array
    {
        $closeIndexes = [
            // si pas line 1 et pas colonne 1
            ($index >= self::SIZE && $index % self::SIZE !== 0) ? $index - self::SIZE - 1 : null, // haut gauche
            // si pas ligne 1
            ($index >= self::SIZE) ? $index - self::SIZE : null, // haut
            // si pas line 1 et pas colonne max
            ($index >= self::SIZE && ($index % self::SIZE) !== (self::SIZE - 1)) ? $index - self::SIZE + 1 : null, // haut droite

            ($index % self::SIZE !== 0) ? $index - 1 : null, // gauche

            (($index % self::SIZE) !== (self::SIZE - 1)) ? $index + 1 : null, // droite

            // Si pas line max et pas colonne 1
            ($index < (self::SIZE ** 2 - self::SIZE) && $index % self::SIZE !== 0) ? $index + self::SIZE - 1 : null, // bas gauche
            // si pas line max
            ($index < (self::SIZE ** 2 - self::SIZE)) ? $index + self::SIZE : null, // bas
            // si pas line max et pas colonne max
            ($index < (self::SIZE ** 2 - self::SIZE) && ($index % self::SIZE) !== (self::SIZE - 1)) ? $index + self::SIZE + 1 : null, // bas droite
        ];

        return array_filter($closeIndexes);
    }
}
