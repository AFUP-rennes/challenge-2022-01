<?php

declare(strict_types=1);

namespace Challenge;

final class Grid
{
    private array $grid = [
        "A" => [0,0,0,0,0,0,0,0,0,0],
        "B" => [0,0,0,0,0,0,0,0,0,0],
        "C" => [0,0,0,0,0,0,0,0,0,0],
        "D" => [0,0,0,0,0,0,0,0,0,0],
        "E" => [0,0,0,0,0,0,0,0,0,0],
        "F" => [0,0,0,0,0,0,0,0,0,0],
        "G" => [0,0,0,0,0,0,0,0,0,0],
        "H" => [0,0,0,0,0,0,0,0,0,0],
        "I" => [0,0,0,0,0,0,0,0,0,0],
        "J" => [0,0,0,0,0,0,0,0,0,0],
    ];

    public function fill(Coordinate $from, Coordinate $to, int $content): void
    {
        $startLetter = $from->getY();
        $endLetter = $to->getY();
        $startInteger = $from->getX()-1;
        $endInteger = $to->getX()-1;
        for ($i = $startLetter; $i <= min(74, $endLetter); $i++) {
            for ($j = $startInteger; $j <= min(10, $endInteger); $j++) {
                $this->grid[chr($i)][$j] = $content;
            }
        }
    }

    private function addMarge(Coordinate $from, Coordinate $to): void
    {
        $from->moveTopSafe();
        $from->moveLeftSafe();
        $to->moveBottomSafe();
        $to->moveRightSafe();
        $this->fill($from, $to, -1);
    }

    public function canAddBoatVertically(Coordinate $coordinates, int $size): bool
    {
        $start = $coordinates->getY();
        $max = $start + $size - 1;
        if ($max > 74) {
            return false;
        }
        for ($i = $start; $i < $max; $i++) {
            if ($this->grid[chr($i)][$coordinates->getX()-1] !== 0) {
                return false;
            }
        }
        return true;
    }

    public function addBoatVertically(Coordinate $coordinate, int $size, int $boatId): void
    {
        $end = (clone $coordinate);
        $end->moveBottom($size-1);
        $this->addMarge(clone $coordinate, clone $end);
        $this->fill($coordinate, $end, $boatId);
    }

    public function canAddBoatHorizontally(Coordinate $coordinate, int $size): bool
    {
        $start = $coordinate->getX()-1;
        $max = $start + $size;
        if ($max > count($this->grid)) {
            return false;
        }
        for ($i = $start; $i < $max; $i++) {
            if ($this->grid[chr($coordinate->getY())][$i] !== 0) {
                return false;
            }
        }
        return true;
    }

    public function addBoatHorizontally(Coordinate $coordinate, int $size, int $boatId): void
    {
        $end = clone $coordinate;
        $end->moveRight($size-1);
        $this->addMarge(clone $coordinate, clone $end);
        $this->fill($coordinate, $end, $boatId);
    }

    public function getCell(Coordinate $coordinate): int
    {
        return $this->grid[chr($coordinate->getY())][$coordinate->getX()-1];
    }

    public function clearCell(Coordinate $coordinate): void
    {
        $this->grid[chr($coordinate->getY())][$coordinate->getX()-1] = 0;
    }
}
