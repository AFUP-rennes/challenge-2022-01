<?php

class Boat
{
    /** @var array<int, int> */
    private array $indexes;

    public function __construct(array $indexes)
    {
        $this->indexes = $indexes;
    }

    public function getIndexes(): array
    {
        return $this->indexes;
    }
}
