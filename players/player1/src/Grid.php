<?php

declare(strict_types=1);

namespace App;

use App\Exception\RuntimeException;
use App\Exception\SunkException;

final class Grid
{
    private const HIT = 2;
    private const HORIZONTAL = 1;
    private const MISS = 1;

    private const OUT_OF_BOUND = null;
    private const SUNK = 3;
    private const UNEXPLORED = 0;

    private const UNKNOWN_ORIENTATION = -1;
    private const VERTICAL = 0;

    /** @var array<int, array<int, ?int>> */
    private array $grid;

    public function __construct(private int $gridSize = 10)
    {
        $this->grid = \array_fill(0, $this->gridSize, \array_fill(0, $this->gridSize, self::UNEXPLORED));
    }

    /**
     * @param int $smallestShip Information about the smallest ship not sunk, so we do not return a coordinate where a
     *                          ship with length of 4 cannot fit if it remains 2 unexplored slots.
     *
     * @throws RuntimeException
     * @throws SunkException
     */
    public function findBestCoordinateToHit(int $smallestShip): Coordinate
    {
        // Is there a "hit" on the grid ?
        $coordinate = $this->findAHit();
        if ($coordinate !== null) {
            return $this->findNextHit($coordinate);
        }

        $unexplored = [];
        $coordinate = new Coordinate(0, 0);
        while ($this->get($coordinate) !== self::OUT_OF_BOUND) {
            if ($this->get($coordinate) === self::UNEXPLORED) {
                if ($this->shipCanFit($coordinate, $smallestShip)) {
                    $unexplored[] = $coordinate;
                    if (\in_array(self::MISS, $this->getMulti(
                        $coordinate->topLeft(),
                        $coordinate->topRight(),
                        $coordinate->bottomLeft(),
                        $coordinate->bottomRight()
                    ), true)) {
                        // If there is some "miss" in any of the four corners, raise the probability (x3) to choose this
                        // coordinate. A checkerboard search has better results.
                        $unexplored[] = $coordinate;
                        $unexplored[] = $coordinate;
                    }
                } else {
                    $this->miss($coordinate); // No ship can fit here. Mark as "miss"
                }
            }
            $coordinate = $coordinate->sailOnGrid($this->gridSize);
        }

        if ($unexplored === []) {
            throw new RuntimeException('No ship can fit in the remaining grid slots. Did you cheat?');
        }

        // Choose randomly into all unexplored coordinates
        return $unexplored[\array_rand($unexplored)];
    }

    /**
     * @return int
     */
    public function getGridSize(): int
    {
        return $this->gridSize;
    }

    /**
     * @return array<int, ?int>
     */
    public function getMulti(Coordinate ...$coordinates): array
    {
        $result = [];

        foreach ($coordinates as $coordinate) {
            $result[] = $this->grid[$coordinate->getX()][$coordinate->getY()] ?? self::OUT_OF_BOUND;
        }

        return $result;
    }

    public function hit(Coordinate $coordinate): void
    {
        // All diagonal slots are "miss"
        $this->miss($coordinate->topLeft());
        $this->miss($coordinate->topRight());
        $this->miss($coordinate->bottomLeft());
        $this->miss($coordinate->bottomRight());

        // hit this slot
        $this->set($coordinate, self::HIT);
    }

    public function miss(Coordinate $coordinate): void
    {
        $this->set($coordinate, self::MISS);
    }

    /**
     * @throws SunkException
     */
    public function sunk(Coordinate $coordinate): int
    {
        $this->hit($coordinate);

        $orientation = $this->getOrientation($coordinate);
        if ($orientation === self::UNKNOWN_ORIENTATION) {
            throw new SunkException('Could not sunk ship : is it a ship with a size of "1" ?');
        }

        // miss front and back of the ship
        $begin = $this->swimUp($coordinate, $orientation, self::HIT);
        $this->set($begin, self::MISS);
        $end = $this->swimDown($begin, $orientation, self::HIT, self::SUNK);
        $this->set($end, self::MISS);

        if ($orientation === self::HORIZONTAL) {
            $size = $end->getX() - $begin->getX() - 1;
        } else {
            $size = $end->getY() - $begin->getY() - 1;
        }

        return $size;
    }

    public function toString(): string
    {
        $grid = '';
        for ($y = 0; $y < $this->gridSize; $y++) {
            for ($x = 0; $x < $this->gridSize; $x++) {
                $state = $this->grid[$x][$y];
                if ($state === self::UNEXPLORED) {
                    $grid .= '.';
                } else {
                    $grid .= $state;
                }
            }
            $grid .= "\n";
        }

        return $grid;
    }

    private function findAHit(): ?Coordinate
    {
        $coordinate = new Coordinate(0, 0);
        while ($this->get($coordinate) !==  self::HIT) {
            if ($this->get($coordinate) === self::OUT_OF_BOUND) {
                return null;
            }
            $coordinate = $coordinate->sailOnGrid($this->gridSize);
        }

        return $coordinate;
    }

    /**
     * @throws SunkException
     */
    private function findNextHit(Coordinate $coordinate): Coordinate
    {
        // Look to another hit to determine the orientation of the ship
        $orientation = $this->getOrientation($coordinate);

        if ($orientation === self::UNKNOWN_ORIENTATION) {
            // It's a "lonely" hit :)
            // No hit around : take one unexplored
            $coordinates = [
                $coordinate->left(),
                $coordinate->top(),
                $coordinate->right(),
                $coordinate->bottom()
            ];

            $unexplored = $this->getMulti(...$coordinates);
            $foundUnexplored = \array_search(self::UNEXPLORED, $unexplored, true);
            if ($foundUnexplored === false) {
                throw new SunkException('Found one hit with misfires all around.');
            }

            return $coordinates[$foundUnexplored];
        }

        // Find the beginning of the ship (rewind)
        $coordinate = $this->swimUp($coordinate, $orientation, self::HIT);
        if ($this->get($coordinate) === self::UNEXPLORED) {
            return $coordinate;
        }

        // Unexplored must be to the opposite side
        $coordinate = $this->swimDown($coordinate, $orientation, self::HIT);
        if ($this->get($coordinate) !== self::UNEXPLORED) {
            throw new SunkException('Found ship with misfires at both ends. Should be sunk');
        }

        return $coordinate;
    }

    private function get(Coordinate $coordinate): ?int
    {
        return $this->grid[$coordinate->getX()][$coordinate->getY()] ?? self::OUT_OF_BOUND;
    }

    private function getOrientation(Coordinate $coordinate): int
    {
        if (\in_array(self::HIT, $this->getMulti($coordinate->left(), $coordinate->right()), true)) {
            return self::HORIZONTAL;
        }

        if (\in_array(self::HIT, $this->getMulti($coordinate->top(), $coordinate->bottom()), true)) {
            return self::VERTICAL;
        }

        return self::UNKNOWN_ORIENTATION;
    }

    private function set(Coordinate $coordinate, ?int $status): void
    {
        if ($coordinate->fitInGrid($this->gridSize)) {
            $this->grid[$coordinate->getX()][$coordinate->getY()] = $status;
        }
    }

    private function shipCanFit(Coordinate $coordinate, int $smallestShip): bool
    {
        $begin = $this->swimUp($coordinate, self::HORIZONTAL, self::UNEXPLORED);
        $end = $this->swimDown($begin, self::HORIZONTAL, self::UNEXPLORED);
        $size = $end->getX() - $begin->getX() - 1;
        if ($size >= $smallestShip) {
            return true;
        }

        $begin = $this->swimUp($coordinate, self::VERTICAL, self::UNEXPLORED);
        $end = $this->swimDown($begin, self::VERTICAL, self::UNEXPLORED);
        $size = $end->getY() - $begin->getY() - 1;

        return $size >= $smallestShip;
    }

    /**
     * Below the $coordinate, swim down the river following the $orientation and $riverType. Return the last coordinate
     * not on the river. (the first position is not on the river).
     * Optionally, change the river type (ex: change "hit" into "sunk")
     *
     * @param Coordinate $coordinate Start at this position
     * @param int $orientation orientation of the ship (horizontal or vertical)
     * @param int $riverType Type to follow
     * @param int|null $changeType Type to change. No change if "null"
     *
     * @return Coordinate last position below the river
     */
    private function swimDown(Coordinate $coordinate, int $orientation, int $riverType, ?int $changeType = null): Coordinate
    {
        $coordinate = $orientation === self::HORIZONTAL ? $coordinate->right() : $coordinate->bottom();
        while ($this->get($coordinate) === $riverType) {
            if ($changeType !== null) {
                $this->set($coordinate, $changeType);
            }
            $coordinate = $orientation === self::HORIZONTAL ? $coordinate->right() : $coordinate->bottom();
        }

        return $coordinate;
    }

    /**
     * From $coordinate, swim up the river following the $orientation and $riverType. Return the last coordinate not on
     * the river.
     * For exemple: follow the "hit" pass from D7, horizontally:
     *     `swimUp(Coordinate::fromString('D7'), self::HORIZONTAL,self::HIT);`
     *
     * @param Coordinate $coordinate Start at this position
     * @param int $orientation orientation of the ship (horizontal or vertical)
     * @param int $riverType Type to follow
     *
     * @return Coordinate last position above the river
     */
    private function swimUp(Coordinate $coordinate, int $orientation, int $riverType): Coordinate
    {
        while ($this->get($coordinate) === $riverType) {
            $coordinate = ($orientation === self::HORIZONTAL ? $coordinate->left() : $coordinate->top());
        }

        return $coordinate;
    }
}
