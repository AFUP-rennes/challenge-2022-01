<?php

declare(strict_types=1);

namespace App;

use App\Exception\SunkException;
use PHPUnit\Framework\TestCase;

final class GridTest extends TestCase
{
    public function test_best_coordinate_for_ship_lengt(): void
    {
        $grid = new Grid();
        // Fill grid and keep 2 "path" (5: F9-J9) and (4: H7-H10)
        for ($y = 0; $y <= $grid->getGridSize(); $y++) {
            for ($x = 0; $x < $grid->getGridSize(); $x++) {
                if ($y === 8 && $x >= 5 && $x <= 9) {
                    continue;
                }
                if ($x === 7 && $y >= 6 && $y <= 9) {
                    continue;
                }
                $grid->miss(new Coordinate($x, $y));
            }
        }

        // It's a random algorithm, do it 100 times to be sure all assertions are valide
        $count = 100;
        while ($count--) {
            $coordinate = $grid->findBestCoordinateToHit(5);
            self::assertEquals(8, $coordinate->getY());
            self::assertGreaterThanOrEqual(5, $coordinate->getX());
            self::assertLessThanOrEqual(9, $coordinate->getX());
        }
    }
    public function test_create_empty(): void
    {
        $grid = new Grid();
        self::assertEquals(<<<GRID
        ..........
        ..........
        ..........
        ..........
        ..........
        ..........
        ..........
        ..........
        ..........
        ..........
        GRID, trim($grid->toString()));
    }

    public function test_create_with_hit(): void
    {
        $grid = new Grid();
        $grid->hit(Coordinate::fromString('B2'));
        $grid->hit(Coordinate::fromString('A10'));
        $grid->hit(Coordinate::fromString('J1'));
        $grid->hit(Coordinate::fromString('D4'));
        $grid->hit(Coordinate::fromString('C4'));
        self::assertEquals(<<<GRID
        1.1......2
        .2......1.
        11111.....
        ..22......
        .1111.....
        ..........
        ..........
        ..........
        .1........
        2.........
        GRID, trim($grid->toString()));
    }

    public function test_create_with_hit_and_sunk(): void
    {
        $grid = new Grid();
        $grid->hit(Coordinate::fromString('B2'));
        $grid->hit(Coordinate::fromString('A10'));
        $grid->hit(Coordinate::fromString('J1'));
        $grid->hit(Coordinate::fromString('D4'));
        $grid->hit(Coordinate::fromString('C4'));
        $grid->sunk(Coordinate::fromString('E4'));
        self::assertEquals(<<<GRID
        1.1......2
        .2......1.
        111111....
        .13331....
        .11111....
        ..........
        ..........
        ..........
        .1........
        2.........
        GRID, trim($grid->toString()));
    }

    public function test_create_with_miss(): void
    {
        $grid = new Grid();
        $grid->miss(Coordinate::fromString('B2'));
        $grid->miss(Coordinate::fromString('A10'));
        $grid->miss(Coordinate::fromString('J1'));
        self::assertEquals(<<<GRID
        .........1
        .1........
        ..........
        ..........
        ..........
        ..........
        ..........
        ..........
        ..........
        1.........
        GRID, trim($grid->toString()));
    }

    public function test_get_bet_coord_on_grid_with_hit(): void
    {
        $grid = new Grid();
        $grid->miss(Coordinate::fromString('D1'));
        $grid->miss(Coordinate::fromString('E2'));
        $grid->miss(Coordinate::fromString('D3'));
        $grid->hit(Coordinate::fromString('D2'));
        $grid->hit(Coordinate::fromString('A4'));
        $coordinate = $grid->findBestCoordinateToHit(2);
        self::assertEquals('C2', $coordinate->toString());
    }

    public function test_lonely_hit_raise_exception(): void
    {
        $grid = new Grid();
        $grid->miss(Coordinate::fromString('D1'));
        $grid->miss(Coordinate::fromString('E2'));
        $grid->miss(Coordinate::fromString('D3'));
        $grid->miss(Coordinate::fromString('C2'));
        $grid->hit(Coordinate::fromString('D2'));
        self::expectException(SunkException::class);
        $grid->findBestCoordinateToHit(2);
    }
}
