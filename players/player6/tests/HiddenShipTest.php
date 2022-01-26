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

use Ouebsson\BattleShip\HiddenShip;
use Ouebsson\BattleShip\Location;
use Ouebsson\BattleShip\LocationList;
use PHPUnit\Framework\TestCase;

class HiddenShipTest extends TestCase {

  /**
   * @dataProvider locationListProvider
   */
  public function testShot(LocationList $locationList): void {
    $ship = new HiddenShip();
    foreach($locationList as $location) {
      $this->assertFalse($ship->getHitShots()->isset($location));
      $this->assertFalse($ship->getLocationList()->isset($location));
      $this->assertTrue($ship->shot($location));
      $this->assertTrue($ship->getHitShots()->isset($location));
      $this->assertTrue($ship->getLocationList()->isset($location));
    }
  }

  /**
   * @dataProvider locationListProvider
   */
  public function testShotAndIsValid(LocationList $locationList): void {
    $ship = new HiddenShip();
    $this->assertFalse($ship->isValid());

    $ship->shot($locationList->rewind());
    $this->assertFalse($ship->isValid());

    foreach(range(2, 5) as $y) {
      $ship->shot($locationList->next());
      $this->assertTrue($ship->isValid());
    }

    $ship->shot($locationList->next());
    $this->assertFalse($ship->isValid());
  }

  /**
   * @dataProvider locationListProvider
   */
  public function testShotAndLength(LocationList $locationList): void {
    $ship = new HiddenShip();
    $l    = 0;
    $this->assertEquals(0, $ship->getLength());
    foreach($locationList as $location) {
      $ship->shot($location);
      $this->assertEquals(++$l, $ship->getLength());
    }
  }

  /**
   * @dataProvider locationListProvider
   */
  public function testIsSunkFalse(LocationList $locationList): void {
    $ship = new HiddenShip();
    $this->assertFalse($ship->isSunk());
    foreach($locationList as $location) {
      $ship->shot($location);
      $this->assertFalse($ship->isSunk());
    }
  }

  /**
   * @dataProvider locationListProvider
   */
  public function testIsSunkTrue(LocationList $locationList): void {
    $ship = new HiddenShip();
    $this->assertFalse($ship->isSunk());
    $ship->setSunk(true);
    $this->assertTrue($ship->isSunk());
    foreach($locationList as $location) {
      $ship->shot($location);
      $this->assertTrue($ship->isSunk());
    }
  }

  public function locationListProvider(): array {
    return [
      [new LocationList([
        new Location(0, 0),
        new Location(0, 1),
        new Location(0, 2),
        new Location(0, 3),
        new Location(0, 4),
        new Location(0, 5),
        new Location(0, 6),
      ]),],
    ];
  }
}