<?php

class Defence
{
    private Layout $layout;

    /** @var array<Boat> */
    private array $boats;

    public function __construct(Layout $layout)
    {
        $this->layout = $layout;
    }

    /**
     * @param array<Boat> $boats
     */
    public function addBoats(array $boats): void
    {
        $this->boats = $boats;
    }

    public function handleFire(int $index): GridIndexKnowledge
    {
        if ($boat = $this->getBoat($index)) {
            $this->layout->setGridIndex($index, GridIndexKnowledge::hit());
            if ($this->isBoatSunk($boat)) {
                $this->layout->setGridIndex($index, GridIndexKnowledge::sunk());
                if (!$this->hasBoatAlive()) {
                    $this->layout->setGridIndex($index, GridIndexKnowledge::won());
                    return GridIndexKnowledge::won();
                }
                return GridIndexKnowledge::sunk();
            }
            return GridIndexKnowledge::hit();
        }
        return GridIndexKnowledge::miss();
    }

    public function isBoatSunk(Boat $boat): bool
    {
        foreach ($boat->getIndexes() as $boatIndex) {
            if ($this->layout->getGridIndex($boatIndex)->equal(GridIndexKnowledge::unknown())) {
                return false;
            }
        }
        return true;
    }

    private function hasBoatAlive(): bool
    {
        foreach ($this->boats as $boat) {
            if (!$this->isBoatSunk($boat)) {
                return true;
            }
        }
        return false;
    }

    private function getBoat(int $index): ?Boat
    {
        foreach ($this->boats as $boat) {
            foreach ($boat->getIndexes() as $boatIndex) {
                if ($index === $boatIndex) {
                    return $boat;
                }
            }
        }

        return null;
    }
}
