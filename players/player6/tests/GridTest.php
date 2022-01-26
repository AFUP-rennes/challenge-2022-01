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
use Ouebsson\BattleShip\Grid;
use Ouebsson\BattleShip\HiddenShip;
use Ouebsson\BattleShip\Location;
use Ouebsson\BattleShip\LocationLine;
use Ouebsson\BattleShip\Ship;
use PHPUnit\Framework\TestCase;

class GridTest extends TestCase {

  public function testAddShip(): void {
    $grid = new Grid();
    $grid->addShip(
      new Ship(
        new LocationLine(
          Location::fromString("A1"),
          2,
          CardinalDirection::EAST)));
    $this->assertCount(1, $grid->getShipList());
  }

  public function testMissedShots(): void {
    $grid = new Grid();
    $this->assertCount(0, $grid->getMissedShotList());
    $grid->miss(Location::fromString("B5"));
    $this->assertCount(1, $grid->getMissedShotList());
    $grid->miss(Location::fromString("B5"));
    $this->assertCount(1, $grid->getMissedShotList());
    $grid->miss(Location::fromString("B6"));
    $this->assertCount(2, $grid->getMissedShotList());
  }

  public function testOtherGridMissAndHitWithOneShip(): void {
    $grid = new Grid();
    $grid->addShip(new HiddenShip());
    $this->assertCount(0, $grid->getMissedShotList());
    $this->assertCount(0, $grid->getHitShotList());
    $this->assertCount(1, $grid->getShipList());
    $this->assertCount(0, $grid->getShipsLocations());
    $this->assertFalse($grid->isWon());

    $grid->miss(Location::fromString("A1"));
    $this->assertCount(1, $grid->getMissedShotList());
    $this->assertCount(0, $grid->getHitShotList());
    $this->assertCount(1, $grid->getShipList());
    $this->assertCount(0, $grid->getShipsLocations());
    $this->assertFalse($grid->isWon());

    $grid->hit(Location::fromString("A2"));
    $this->assertCount(1, $grid->getMissedShotList());
    $this->assertCount(1, $grid->getHitShotList());
    $this->assertCount(1, $grid->getShipList());
    $this->assertCount(1, $grid->getShipsLocations());
    $this->assertFalse($grid->isWon());

    $grid->hit(Location::fromString("A3"));
    $this->assertCount(1, $grid->getMissedShotList());
    $this->assertCount(2, $grid->getHitShotList());
    $this->assertCount(1, $grid->getShipList());
    $this->assertCount(2, $grid->getShipsLocations());
    $this->assertFalse($grid->isWon());

    $grid->miss(Location::fromString("B1"));
    $this->assertCount(2, $grid->getMissedShotList());
    $this->assertCount(2, $grid->getHitShotList());
    $this->assertCount(1, $grid->getShipList());
    $this->assertCount(2, $grid->getShipsLocations());
    $this->assertFalse($grid->isWon());

    $grid->hit(Location::fromString("A4"))->setSunk(true);
    $this->assertCount(2, $grid->getMissedShotList());
    $this->assertCount(3, $grid->getHitShotList());
    $this->assertCount(1, $grid->getShipList());
    $this->assertCount(3, $grid->getShipsLocations());
    $this->assertTrue($grid->isWon());
  }

  public function testOtherGridMissAndHitWithFiveShip(): void {
    $grid = new Grid();
    $grid->addShip(new HiddenShip());
    $grid->addShip(new HiddenShip());
    $grid->addShip(new HiddenShip());
    $grid->addShip(new HiddenShip());
    $grid->addShip(new HiddenShip());
    $this->assertFalse($grid->isWon());

    foreach($grid->getShipList() as $ship) {
      $this->assertFalse($ship->isSunk());
    }

    foreach(range(0, 4) as $noShip) {
      for($x = 0; $x < 2; $x++) {
        $ship = $grid->hit(new Location($x, $noShip));
        $this->assertInstanceOf(HiddenShip::class, $ship);
      }
      $this->assertFalse($grid->isWon());
      $ship->setSunk(true);
    }
    $this->assertTrue($grid->isWon());
  }
}
