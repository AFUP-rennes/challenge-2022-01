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
use Ouebsson\BattleShip\LocationList;
use PHPUnit\Framework\TestCase;

final class LocationTest extends TestCase {

  public function testNewLocation(): Location {
    $x        = mt_rand(0, 9);
    $y        = mt_rand(0, 9);
    $location = new Location($x, $y);
    $this->assertEquals($location->getX(), $x);
    $this->assertEquals($location->getY(), $y);
    return $location;
  }

  public function testIsValidOrNot(): void {
    for($x = 0; $x < 10; $x++) {
      for($y = 0; $y < 10; $y++) {
        $this->assertTrue((new Location($x, $y))->isValid());
      }
    }
    $this->assertFalse((new Location(-1, -1))->isValid());
    $this->assertFalse((new Location(0, -1))->isValid());
    $this->assertFalse((new Location(-1, 0))->isValid());
    $this->assertFalse((new Location(0, 10))->isValid());
    $this->assertFalse((new Location(10, 0))->isValid());
    $this->assertFalse((new Location(10, 10))->isValid());
  }

  /**
   * @depends testNewLocation
   */
  public function testToString(Location $location): void {
    $x = $location->getX();
    $y = $location->getY();
    $this->assertEquals(sprintf("%s%d", chr(65 + $x), $y + 1), "$location");
  }

  public function testFromString(): void {
    $p1 = Location::fromString("A1");
    $this->assertInstanceOf(Location::class, $p1);
    $this->assertEquals(0, $p1->getX());
    $this->assertEquals(0, $p1->getY());
    $this->assertTrue($p1->isValid());

    $p2 = Location::fromString("J10");
    $this->assertInstanceOf(Location::class, $p2);
    $this->assertEquals(9, $p2->getX());
    $this->assertEquals(9, $p2->getY());
    $this->assertTrue($p2->isValid());

    $p3 = Location::fromString("C4");
    $this->assertInstanceOf(Location::class, $p3);
    $this->assertEquals(2, $p3->getX());
    $this->assertEquals(3, $p3->getY());
    $this->assertTrue($p3->isValid());
  }

  public function testAllPossiblesFromStringAndToString(): void {
    $x = 0;
    foreach(["A", "B", "C", "D", "E", "F", "G", "H", "I", "J"] as $col) {
      $y = 0;
      for($row = 1; $row <= 10; $row++) {
        $pt = Location::fromString("$col$row");
        $this->assertInstanceOf(Location::class, $pt);
        $this->assertEquals($x, $pt->getX());
        $this->assertEquals($y, $pt->getY());
        $this->assertEquals("$col$row", (string) (new Location($x, $y)));
        $y++;
      }
      $x++;
    }
  }

  public function testEqualities(): void {
    $pA = new Location(0, 0);
    $pB = new Location(0, 1);
    $pC = new Location(1, 0);
    $this->assertTrue($pA->equals($pA));
    $this->assertTrue($pB->equals($pB));
    $this->assertTrue($pC->equals($pC));

    $this->assertFalse($pA->equals($pB));
    $this->assertFalse($pB->equals($pC));
    $this->assertFalse($pC->equals($pA));
  }

  public function testAdjacentLocationList(): void {
    foreach(["B2", "C4", "E6", "I9"] as $pL) {
      $pC   = Location::fromString($pL);
      $list = LocationList::fromGenerator($pC->getAdjacentLocations());
      $this->assertCount(8, $list);
      $this->assertFalse($list->isset($pC));
    }

    foreach(["A1", "A10", "J1", "J10"] as $pL) {
      $pC   = Location::fromString($pL);
      $list = LocationList::fromGenerator($pC->getAdjacentLocations());
      $this->assertCount(3, $list);
      $this->assertFalse($list->isset($pC));
    }

    foreach(["A3", "C1", "J4", "D10"] as $pL) {
      $pC   = Location::fromString($pL);
      $list = LocationList::fromGenerator($pC->getAdjacentLocations());
      $this->assertCount(5, $list);
      $this->assertFalse($list->isset($pC));
    }
  }

  public function testClone(): void {
    foreach(range(0, 9) as $x) {
      foreach(range(0, 9) as $y) {
        $origine = new Location($x, $y);
        $clone   = $origine->clone();
        $this->assertFalse($origine === $clone);
        $this->assertEquals("$origine", "$clone");
        $this->assertTrue($origine->equals($clone));
        $this->assertTrue($clone->equals($origine));
      }
    }
  }

  public function testLocationToStringMatchesRegularExpression(): void {
    foreach(range(0, 9) as $x) {
      foreach(range(0, 9) as $y) {
        $this->assertMatchesRegularExpression(
          '#^([A-J](?:[1-9]|10))$#i',
          (string) new Location($x, $y)
        );
      }
    }
  }

  public function testNextValidLocation(): void {
    $this->assertEquals("B1", Location::fromString("B2")->next(CardinalDirection::NORTH));
    $this->assertEquals("C2", Location::fromString("B2")->next(CardinalDirection::EAST));
    $this->assertEquals("B3", Location::fromString("B2")->next(CardinalDirection::SOUTH));
    $this->assertEquals("A2", Location::fromString("B2")->next(CardinalDirection::WEST));

    $this->assertNotFalse(Location::fromString("B2")->next(CardinalDirection::NORTH));
    $this->assertNotFalse(Location::fromString("B2")->next(CardinalDirection::EAST));
    $this->assertNotFalse(Location::fromString("B2")->next(CardinalDirection::SOUTH));
    $this->assertNotFalse(Location::fromString("B2")->next(CardinalDirection::WEST));
  }

  public function testNextFalse(): void {
    $this->assertFalse(Location::fromString("C1")->next(CardinalDirection::NORTH));
    $this->assertFalse(Location::fromString("J4")->next(CardinalDirection::EAST));
    $this->assertFalse(Location::fromString("D10")->next(CardinalDirection::SOUTH));
    $this->assertFalse(Location::fromString("A5")->next(CardinalDirection::WEST));
  }
}