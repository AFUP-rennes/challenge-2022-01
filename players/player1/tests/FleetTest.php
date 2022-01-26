<?php

declare(strict_types=1);

namespace App;

use App\Exception\ShipOutOfBoundException;
use App\Exception\ShipOverlapsAnotherException;
use PHPUnit\Framework\TestCase;

final class FleetTest extends TestCase
{
    public function test_create_fleet(): void
    {
        $ship1 = new Ship(new Coordinate(0, 0), 3, Ship::ORIENTATION_HORIZONTAL);
        $ship2 = new Ship(new Coordinate(0, 2), 2, Ship::ORIENTATION_VERTICAL);
        $fleet = new Fleet(10);
        $fleet->addShip($ship1);
        $fleet->addShip($ship2);

        self::assertEquals($ship1, $fleet->getShipAt(new Coordinate(2, 0)));
        self::assertEquals($ship2, $fleet->getShipAt(new Coordinate(0, 3)));
        self::assertNull($fleet->getShipAt(new Coordinate(3, 0)));
        self::assertNull($fleet->getShipAt(new Coordinate(0, 4)));

        self::assertFalse($fleet->hasFleetBeenSunk());

        $ship1->damage(new Coordinate(0, 0));
        $ship1->damage(new Coordinate(1, 0));
        $ship1->damage(new Coordinate(2, 0));

        $ship2->damage(new Coordinate(0, 3));
        $ship2->damage(new Coordinate(0, 2));

        self::assertTrue($fleet->hasFleetBeenSunk());
    }

    public function test_create_fleet_with_ship_out_of_grid(): void
    {
        self::expectException(ShipOutOfBoundException::class);
        $fleet = new Fleet(5);
        $fleet->addShip(new Ship(new Coordinate(2, 0), 5, Ship::ORIENTATION_HORIZONTAL));
    }

    public function test_create_fleet_with_ship_to_close(): void
    {
        self::expectException(ShipOverlapsAnotherException::class);
        $fleet = new Fleet(10);
        $fleet->addShip(new Ship(new Coordinate(0, 0), 3, Ship::ORIENTATION_HORIZONTAL));
        $fleet->addShip(new Ship(new Coordinate(0, 1), 2, Ship::ORIENTATION_VERTICAL));
    }
}
