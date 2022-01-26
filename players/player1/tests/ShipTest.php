<?php

declare(strict_types=1);

namespace App;

use PHPUnit\Framework\TestCase;

final class ShipTest extends TestCase
{
    public function test_create(): void
    {
        $ship = new Ship(Coordinate::fromString('B2'), 3, Ship::ORIENTATION_HORIZONTAL);
        $result = '';
        foreach ($ship->getCoordinates() as $coordinate) {
            $result .= $coordinate->toString();
        }

        self::assertEquals('B2C2D2', $result);
        self::assertFalse($ship->isSunk());

        self::assertTrue($ship->isHitBy(Coordinate::fromString('B2')));
        self::assertTrue($ship->isHitBy(Coordinate::fromString('C2')));
        self::assertTrue($ship->isHitBy(Coordinate::fromString('D2')));
        self::assertFalse($ship->isHitBy(Coordinate::fromString('E2')));
    }

    public function test_damage(): void
    {
        $ship = new Ship(Coordinate::fromString('C1'), 3, Ship::ORIENTATION_VERTICAL);
        $ship->damage(Coordinate::fromString('C2'));
        self::assertFalse($ship->isSunk());
        $ship->damage(Coordinate::fromString('C1'));
        self::assertFalse($ship->isSunk());
        $ship->damage(Coordinate::fromString('C3'));
        self::assertTrue($ship->isSunk());
    }

    public function test_ship_not_too_close(): void
    {
        $ship = new Ship(Coordinate::fromString('B2'), 3, Ship::ORIENTATION_HORIZONTAL);
        self::assertTrue($ship->isClosedTo(new Ship(Coordinate::fromString('B3'), 3, Ship::ORIENTATION_VERTICAL)));
        self::assertTrue($ship->isClosedTo(new Ship(Coordinate::fromString('A3'), 3, Ship::ORIENTATION_VERTICAL)));
        self::assertFalse($ship->isClosedTo(new Ship(Coordinate::fromString('B4'), 3, Ship::ORIENTATION_VERTICAL)));

        // Overlap
        self::assertTrue($ship->isClosedTo(new Ship(Coordinate::fromString('C1'), 3, Ship::ORIENTATION_VERTICAL)));
    }
}
