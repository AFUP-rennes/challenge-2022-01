<?php

class BigBerthaBattle
{
    private int $lastShoot;
    private int $currentTarget = 0;

    public function fire(int $index): void
    {
        $this->currentTarget = $index;
        $this->lastShoot = $index;
        echo Coord::fromIndex($index).PHP_EOL;
    }

    public function getLastShoot(): int
    {
        return $this->lastShoot;
    }

    public function setCurrentTarget(int $currentTarget): void
    {
        $this->currentTarget = $currentTarget;
    }

    public function getCurrentTarget(): int
    {
        return $this->currentTarget;
    }
}
