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

use Ouebsson\BattleShip\Location;
use Ouebsson\BattleShip\LocationList;
use Ouebsson\BattleShip\Ship;
use PHPUnit\Framework\TestCase;

final class ShipTest extends TestCase {

  public function testNormalizeLength(): void {
    foreach(range(2, 5) as $l) {
      $this->assertEquals($l, Ship::normalizedLength($l));
    }
    foreach(range(2 - 5, 2) as $l) {
      $this->assertEquals(2, Ship::normalizedLength($l));
    }
    foreach(range(5, 5 + 5) as $l) {
      $this->assertEquals(5, Ship::normalizedLength($l));
    }
  }

  /**
   * @dataProvider locationListProvider
   */
  public function testLength($locationList): void {
    for($l = 1; $l < 8; $l++) {
      $this->assertEquals($l, (new Ship(new LocationList(array_slice($locationList, 0, $l))))->getLength());
    }
  }

  /**
   * @dataProvider locationListProvider
   */
  public function testIsValid($locationList) {
    for($l = 2; $l < 6; $l++) {
      $this->assertTrue((new Ship(new LocationList(array_slice($locationList, 0, $l))))->isValid());
    }
    foreach([1, 6, 7] as $l) {
      $this->assertFalse((new Ship(new LocationList(array_slice($locationList, 0, $l))))->isValid());
    }
  }

  /**
   * @dataProvider locationListProvider
   */
  public function testShot($locationList) {
    $locationList = new LocationList(array_slice($locationList, 0, 5));
    $ship         = new Ship($locationList);
    foreach($locationList as $location) {
      $this->assertTrue($ship->shot($location));
    }
    $this->assertFalse($ship->shot(new Location(4, 4)));
    $this->assertFalse($ship->shot(new Location(4, 0)));
  }

  /**
   * @dataProvider locationListProvider
   */
  public function testIsSunkShip($locationList) {
    $locationList = new LocationList(array_slice($locationList, 0, 5));
    $ship         = new Ship($locationList);
    $countHits    = 0;
    foreach($locationList as $location) {
      $this->assertFalse($ship->isSunk());
      $this->assertCount($countHits, $ship->getHitShots());
      $this->assertTrue($ship->shot($location));
      $this->assertCount(++$countHits, $ship->getHitShots());
    }
    $this->assertTrue($ship->isSunk());
  }

  public function locationListProvider(): array {
    return [
      [[
         new Location(0, 0),
         new Location(0, 1),
         new Location(0, 2),
         new Location(0, 3),
         new Location(0, 4),
         new Location(0, 5),
         new Location(0, 6),
       ],],
    ];
  }

}