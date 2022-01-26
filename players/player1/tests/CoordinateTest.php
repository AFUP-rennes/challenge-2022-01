<?php

declare(strict_types=1);

namespace App;

use PHPUnit\Framework\TestCase;

class CoordinateTest extends TestCase
{
    public function test_cardinality(): void
    {
        $coordinate = new Coordinate(7, 5);
        $new = $coordinate->bottom();
        self::assertEquals(7, $new->getX());
        self::assertEquals(6, $new->getY());
        // Immutability : coordinate has not change
        self::assertEquals(7, $coordinate->getX());
        self::assertEquals(5, $coordinate->getY());

        $new = $coordinate->bottomRight();
        self::assertEquals(8, $new->getX());
        self::assertEquals(6, $new->getY());
        // Immutability : coordinate has not change
        self::assertEquals(7, $coordinate->getX());
        self::assertEquals(5, $coordinate->getY());

        $new = $coordinate->bottomLeft();
        self::assertEquals(6, $new->getX());
        self::assertEquals(6, $new->getY());
        // Immutability : coordinate has not change
        self::assertEquals(7, $coordinate->getX());
        self::assertEquals(5, $coordinate->getY());

        $new = $coordinate->left();
        self::assertEquals(6, $new->getX());
        self::assertEquals(5, $new->getY());
        // Immutability : coordinate has not change
        self::assertEquals(7, $coordinate->getX());
        self::assertEquals(5, $coordinate->getY());

        $new = $coordinate->right();
        self::assertEquals(8, $new->getX());
        self::assertEquals(5, $new->getY());
        // Immutability : coordinate has not change
        self::assertEquals(7, $coordinate->getX());
        self::assertEquals(5, $coordinate->getY());

        $new = $coordinate->top();
        self::assertEquals(7, $new->getX());
        self::assertEquals(4, $new->getY());
        // Immutability : coordinate has not change
        self::assertEquals(7, $coordinate->getX());
        self::assertEquals(5, $coordinate->getY());

        $new = $coordinate->topLeft();
        self::assertEquals(6, $new->getX());
        self::assertEquals(4, $new->getY());
        // Immutability : coordinate has not change
        self::assertEquals(7, $coordinate->getX());
        self::assertEquals(5, $coordinate->getY());

        $new = $coordinate->topRight();
        self::assertEquals(8, $new->getX());
        self::assertEquals(4, $new->getY());
        // Immutability : coordinate has not change
        self::assertEquals(7, $coordinate->getX());
        self::assertEquals(5, $coordinate->getY());
    }

    public function test_equality(): void
    {
        $coordinate = new Coordinate(1, 2);
        $this->assertEquals(1, $coordinate->getX());
        $this->assertEquals(2, $coordinate->getY());
    }

    public function test_fit_in_grid(): void
    {
        $coordinate = new Coordinate(7, 5);
        self::assertTrue($coordinate->fitInGrid(10));
        self::assertTrue($coordinate->fitInGrid(8));
        self::assertFalse($coordinate->fitInGrid(7));
        self::assertFalse($coordinate->fitInGrid(3));

        $coordinate = new Coordinate(5, 7);
        self::assertTrue($coordinate->fitInGrid(10));
        self::assertTrue($coordinate->fitInGrid(8));
        self::assertFalse($coordinate->fitInGrid(7));
        self::assertFalse($coordinate->fitInGrid(3));
    }
    public function test_fromString(): void
    {
        $coordinate = Coordinate::fromString('B3');
        $this->assertEquals(1, $coordinate->getX());
        $this->assertEquals(2, $coordinate->getY());

        $coordinate = Coordinate::fromString('A1');
        $this->assertEquals(0, $coordinate->getX());
        $this->assertEquals(0, $coordinate->getY());

        // walk through the alphabet
        for ($x = 0; $x < 26; $x++) {
            for ($y = 0; $y < 26; $y++) {
                $coordinate = Coordinate::fromString(chr(65 + $x) . ($y + 1));
                $this->assertEquals($x, $coordinate->getX());
                $this->assertEquals($y, $coordinate->getY());
            }
        }
    }

    public function test_is_close_to_another_position(): void
    {
        $coordinate = new Coordinate(7, 5);
        self::assertTrue($coordinate->isClosedTo(new Coordinate(8, 5)));
        self::assertTrue($coordinate->isClosedTo(new Coordinate(8, 6)));
        self::assertTrue($coordinate->isClosedTo(new Coordinate(7, 6)));
        self::assertTrue($coordinate->isClosedTo(new Coordinate(6, 6)));
        self::assertTrue($coordinate->isClosedTo(new Coordinate(6, 5)));
        self::assertTrue($coordinate->isClosedTo(new Coordinate(6, 4)));
        self::assertTrue($coordinate->isClosedTo(new Coordinate(7, 4)));

        self::assertFalse($coordinate->isClosedTo(new Coordinate(8, 7)));
        self::assertFalse($coordinate->isClosedTo(new Coordinate(7, 3)));
        self::assertFalse($coordinate->isClosedTo(new Coordinate(1, 1)));
        self::assertFalse($coordinate->isClosedTo(new Coordinate(0, 0)));
    }

    public function test_sail(): void
    {
        $coordinate = new Coordinate(7, 5);
        $new = $coordinate->sailOnGrid(9);
        self::assertEquals(8, $new->getX());
        self::assertEquals(5, $new->getY());
        // Immutability : coordinate has not change
        self::assertEquals(7, $coordinate->getX());
        self::assertEquals(5, $coordinate->getY());

        $coordinate = new Coordinate(7, 5);
        $new = $coordinate->sailOnGrid(8);
        self::assertEquals(0, $new->getX());
        self::assertEquals(6, $new->getY());
        // Immutability : coordinate has not change
        self::assertEquals(7, $coordinate->getX());
        self::assertEquals(5, $coordinate->getY());
    }

    public function test_toString(): void
    {
        $coordinate = new Coordinate(1, 2);
        $this->assertEquals('B3', $coordinate->toString());
    }
}
