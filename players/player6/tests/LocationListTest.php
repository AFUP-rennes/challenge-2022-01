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
use PHPUnit\Framework\TestCase;

class LocationListTest extends TestCase {

  public function testConstructListWithOnlyBadValues(): void {
    $list = new LocationList(range(1, 5));
    $this->assertCount(0, $list);
  }

  public function testConstructListWithPartialBadValues(): void {
    $list = new LocationList([
      "A1",
      Location::fromString("A2"),
      Location::fromString("A3"),
      Location::fromString("A4"),
      "A5",
    ]);
    $this->assertCount(3, $list);
  }

  public function testConstructListWithOnlyGoodValues(): LocationList {
    $list = new LocationList([
      Location::fromString("A1"),
      Location::fromString("A2"),
      Location::fromString("A3"),
      Location::fromString("A4"),
      Location::fromString("A5"),
    ]);
    $this->assertCount(5, $list);

    return $list;
  }

  /**
   * @depends testConstructListWithOnlyGoodValues
   */
  public function testAppendOnce(LocationList $locationList): LocationList {
    $count = $locationList->count();
    $locationList->append(Location::fromString("A6"));
    $this->assertCount($count + 1, $locationList);
    return $locationList;
  }

  /**
   * @depends testAppendOnce
   */
  public function testAppendSameMultiple(LocationList $locationList): LocationList {
    $count = $locationList->count();
    $locationList->append(Location::fromString("A7"));
    $locationList->append(Location::fromString("A7"));
    $locationList->append(Location::fromString("A7"));
    $this->assertCount($count + 1, $locationList);
    return $locationList;
  }

  /**
   * @depends testAppendSameMultiple
   */
  public function testAppendMultiple(LocationList $locationList): LocationList {
    $count = $locationList->count();
    $locationList->append(Location::fromString("B1"));
    $locationList->append(Location::fromString("B2"));
    $locationList->append(Location::fromString("B3"));
    $this->assertCount($count + 3, $locationList);
    return $locationList;
  }

  /**
   * @depends testAppendMultiple
   */
  public function testListKeysAreStringifiedLocationList(LocationList $locationList): void {
    foreach($locationList as $key => $location) {
      $this->assertEquals("$location", $key);
    }
  }

  public function testIsset(): void {
    $location = Location::fromString("A1");
    $list     = new LocationList();
    $this->assertFalse($list->isset($location));

    $list->append($location);
    $this->assertTrue($list->isset($location));
  }

  public function testFromGenerator(): void {
    $list1 = LocationList::fromGenerator(
      Location::fromString("C3")->getAdjacentLocations()
    );
    $this->assertInstanceOf(
      LocationList::class,
      $list1
    );
    $this->assertCount(8, $list1);

    $list2 = LocationList::fromGenerator(
      (function(): Generator {
        yield Location::fromString("A1");
        yield Location::fromString("A2");
        yield Location::fromString("A3");
      })()
    );
    $this->assertInstanceOf(
      LocationList::class,
      $list2
    );
    $this->assertCount(3, $list2);
  }

  public function testIsNotValidOnEmpty(): void {
    $this->assertFalse((new LocationList())->isValid());
  }

  public function testIsValid(): void {
    $list = LocationList::fromGenerator(
      (function(): Generator {
        yield Location::fromString("A1");
        yield Location::fromString("A2");
        yield Location::fromString("A3");
      })()
    );
    $this->assertTrue($list->isValid());

    $list->append(new Location(9, 9));
    $this->assertTrue($list->isValid());

    $list->append(new Location(-9, 9));
    $this->assertFalse($list->isValid());

    $list->append(new Location(0, 0));
    $this->assertFalse($list->isValid());
  }

  public function testXor(): void {
    $listA = new LocationList([
      Location::fromString("A1"),
      Location::fromString("A2"),
      Location::fromString("A3"),
    ]);
    $listB = new LocationList([
      Location::fromString("B1"),
      Location::fromString("C1"),
      Location::fromString("D1"),
    ]);
    $this->assertFalse((new LocationList())->xorLocationList(new LocationList()));
    $this->assertTrue($listA->xorLocationList($listB));
    $this->assertTrue($listB->xorLocationList($listA));
    $listB->append(Location::fromString("A3"));
    $this->assertFalse($listA->xorLocationList($listB));
    $this->assertFalse($listB->xorLocationList($listA));
  }
}