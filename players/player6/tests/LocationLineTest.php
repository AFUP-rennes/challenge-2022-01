<?php declare(strict_types = 1);

/**
 * This file is part of Ouebsson/BattleShip.
 *
 * Ouebsson/BattleShip is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * Ouebsson/BattleShip is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Ouebsson/BattleShip. If not, see <https://www.gnu.org/licenses/>.
 *
 * Copyright 2022 Jonathan BURON <jonathan@ouebsson.fr>
 */

use Ouebsson\BattleShip\CardinalDirection;
use Ouebsson\BattleShip\Location;
use Ouebsson\BattleShip\LocationLine;
use PHPUnit\Framework\TestCase;

class LocationLineTest extends TestCase {

  public function testConstructLineNorth(): void {
    $list = new LocationLine(Location::fromString("E5"), 2, CardinalDirection::NORTH);
    $this->assertCount(2, $list);
    $this->assertTrue($list->isset(Location::fromString("E5")));
    $this->assertTrue($list->isset(Location::fromString("E4")));
    $this->assertFalse($list->isset(Location::fromString("E3")));
  }

  public function testConstructLineWest(): void {
    $list = new LocationLine(
      Location::fromString("J1"),
      5,
      CardinalDirection::WEST
    );
    $this->assertCount(5, $list);
    $this->assertTrue($list->isset(Location::fromString("J1")));
    $this->assertTrue($list->isset(Location::fromString("I1")));
    $this->assertTrue($list->isset(Location::fromString("H1")));
    $this->assertTrue($list->isset(Location::fromString("G1")));
    $this->assertTrue($list->isset(Location::fromString("F1")));
    $this->assertFalse($list->isset(Location::fromString("E1")));
  }

  public function testIsValid(): void {
    $this->assertTrue(
      (new LocationLine(
        Location::fromString("A1"),
        5,
        CardinalDirection::EAST
      ))->isValid()
    );
    $this->assertTrue(
      (new LocationLine(
        Location::fromString("A1"),
        5,
        CardinalDirection::SOUTH
      ))->isValid()
    );
    $this->assertTrue(
      (new LocationLine(
        Location::fromString("J1"),
        5,
        CardinalDirection::WEST
      ))->isValid()
    );
    $this->assertTrue(
      (new LocationLine(
        Location::fromString("J1"),
        5,
        CardinalDirection::SOUTH
      ))->isValid()
    );
    $this->assertTrue(
      (new LocationLine(
        Location::fromString("J10"),
        5,
        CardinalDirection::NORTH
      ))->isValid()
    );
    $this->assertTrue(
      (new LocationLine(
        Location::fromString("J10"),
        5,
        CardinalDirection::WEST
      ))->isValid()
    );
    $this->assertTrue(
      (new LocationLine(
        Location::fromString("A10"),
        5,
        CardinalDirection::EAST
      ))->isValid()
    );
    $this->assertTrue(
      (new LocationLine(
        Location::fromString("A10"),
        5,
        CardinalDirection::NORTH
      ))->isValid()
    );
  }

  public function testIsValidOutgoing(): void {
    $this->assertTrue(
      (new LocationLine(
        Location::fromString("A1"),
        5,
        CardinalDirection::WEST
      ))->isValid()
    );
    $this->assertTrue(
      (new LocationLine(
        Location::fromString("A1"),
        5,
        CardinalDirection::NORTH
      ))->isValid()
    );
    $this->assertTrue(
      (new LocationLine(
        Location::fromString("J1"),
        5,
        CardinalDirection::EAST
      ))->isValid()
    );
    $this->assertTrue(
      (new LocationLine(
        Location::fromString("J1"),
        5,
        CardinalDirection::NORTH
      ))->isValid()
    );
    $this->assertTrue(
      (new LocationLine(
        Location::fromString("J10"),
        5,
        CardinalDirection::SOUTH
      ))->isValid()
    );
    $this->assertTrue(
      (new LocationLine(
        Location::fromString("J10"),
        5,
        CardinalDirection::EAST
      ))->isValid()
    );
    $this->assertTrue(
      (new LocationLine(
        Location::fromString("A10"),
        5,
        CardinalDirection::WEST
      ))->isValid()
    );
    $this->assertTrue(
      (new LocationLine(
        Location::fromString("A10"),
        5,
        CardinalDirection::SOUTH
      ))->isValid()
    );
  }

  public function testValidOnSameLineVertical(): void {
    $loc  = Location::fromString("C3");
    $line = new LocationLine(
      $loc,
      3,
      CardinalDirection::SOUTH
    );
    $this->assertTrue($line->isValid());

    $line->append(new Location($loc->getX(), $loc->getY() + 5));
    $this->assertTrue($line->isValid());

    $line->append(new Location($loc->getX(), $loc->getY() - 2));
    $this->assertTrue($line->isValid());

    $line->append(new Location($loc->getX() + 1, $loc->getY()));
    $this->assertFalse($line->isValid());
  }

  public function testValidOnSameLineHorizontal(): void {
    $loc  = Location::fromString("C3");
    $line = new LocationLine(
      $loc,
      3,
      CardinalDirection::EAST
    );
    $this->assertTrue($line->isValid());

    $line->append(new Location($loc->getX() + 5, $loc->getY()));
    $this->assertTrue($line->isValid());

    $line->append(new Location($loc->getX() - 2, $loc->getY()));
    $this->assertTrue($line->isValid());

    $line->append(new Location($loc->getX(), $loc->getY() + 1));
    $this->assertFalse($line->isValid());
  }

}