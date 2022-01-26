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

namespace Ouebsson\BattleShip;

use loophp\collection\Collection;
use Ouebsson\BattleShip\Contract\ShipInterface;
use function PHPUnit\Framework\throwException;

final class Grid {

  /**
   * @var ShipInterface[]
   */
  private array $shipList = [];

  private LocationList $missedShotList;

  private LocationList $hitShotList;

  public function __construct() {
    $this->missedShotList = new LocationList();
    $this->hitShotList    = new LocationList();
  }

  /**
   * Add a new ship.
   *
   * @param ShipInterface $ship
   * @return void
   */
  public function addShip(ShipInterface $ship): void {
    $this->shipList[] = $ship;
  }

  /**
   * Mark the 1st unsunken hidden ship as hit at this location, regardless
   * of whether it is adjacent to the other locations found.
   * Then returns the result of a normal hit or no hit, if no hidden boat
   * is present the hit will only be considered as such if a ship is actually
   * at that location.
   *
   * @param Location $location
   * @return HiddenShip
   */
  public function hit(Location $location): HiddenShip {
    foreach($this->shipList as $ship) {
      if($ship instanceof HiddenShip && !$ship->isSunk()) {
        if($ship->shot($location)) {
          $this->hitShotList->append($location);
          return $ship;
        }
      }
    }
    return new HiddenShip();
//    die("error All opponents ships are already sunk !");
  }

  /**
   * Try to shoot a ship, return the hit ship if any, otherwise return false.
   *
   * @param Location $location
   * @return ShipInterface|false
   */
  public function shot(Location $location): ShipInterface|false {
    foreach($this->shipList as $ship) {
      if($ship->shot($location)) {
        $this->hitShotList->append($location);
        return $ship;
      }
    }
    return false;
  }

  public function miss(Location $location): void {
    $this->missedShotList->append($location);
  }

  /**
   * Return true if all ships are sunk.
   *
   * @return bool
   */
  public function isWon(): bool {
    foreach($this->shipList as $ship) {
      if(!$ship->isSunk()) {
        return false;
      }
    }
    return true;
  }

  public function __toString(): string {
    $lines = [];
    for($y = 0; $y < 10; $y++) {
      $line = [];
      for($x = 0; $x < 10; $x++) {
        $location = new Location($x, $y);
        $cell     = "   ";
        # Default values.
        foreach($this->shipList as $shipNo => $ship) {
          if($ship->getLocationList()->isset($location)) {
            $cell = "\033[32;1m".($shipNo > 99 ? $shipNo : sprintf("%2s ", $shipNo))."\033[0m";
            if($this->hitShotList->isset($location)) {
              $cell = "\033[41;37;1m░H░\033[0m";
            }
            if($ship->isSunk()) {
              $cell = "\033[42;37;1m░S░\033[0m";
            }
            break;
          }else if($ship->getLocationList()->getBorderedList()->isset($location)) {
            $cell = "\033[37m░░░\033[0m";
          }
        }
        # Hit/miss values.
        if($this->missedShotList->isset($location)) {
          $cell = "\033[33;1m░M░\033[0m";
        }
        $line[$x] = $cell;
      }
      $lines[] = implode('│', $line);
    }
    return <<<OUT
     A   B   C   D   E   F   G   H   I   J  
   ╭───┬───┬───┬───┬───┬───┬───┬───┬───┬───╮
 1 │$lines[0]│
   ├───┼───┼───┼───┼───┼───┼───┼───┼───┼───┤
 2 │$lines[1]│
   ├───┼───┼───┼───┼───┼───┼───┼───┼───┼───┤
 3 │$lines[2]│
   ├───┼───┼───┼───┼───┼───┼───┼───┼───┼───┤
 4 │$lines[3]│
   ├───┼───┼───┼───┼───┼───┼───┼───┼───┼───┤
 5 │$lines[4]│
   ├───┼───┼───┼───┼───┼───┼───┼───┼───┼───┤
 6 │$lines[5]│
   ├───┼───┼───┼───┼───┼───┼───┼───┼───┼───┤
 7 │$lines[6]│
   ├───┼───┼───┼───┼───┼───┼───┼───┼───┼───┤
 8 │$lines[7]│
   ├───┼───┼───┼───┼───┼───┼───┼───┼───┼───┤
 9 │$lines[8]│
   ├───┼───┼───┼───┼───┼───┼───┼───┼───┼───┤
10 │$lines[9]│
   ╰───┴───┴───┴───┴───┴───┴───┴───┴───┴───╯
OUT;
  }

  /**
   * Return a copy of the ship list.
   *
   * @return Ship[]
   */
  public function getShipList(): array {
    return Collection::fromIterable($this->shipList)->all();
  }

  /**
   * Returns a copy of the list of known locations of all ships.
   *
   * @return LocationList
   */
  public function getShipsLocations(): LocationList {
    $list = new LocationList();
    foreach($this->shipList as $ship) {
      $list->appendList($ship->getLocationList());
    }
    return $list;
  }

  public function getMissedShotList(): LocationList {
    return new LocationList($this->missedShotList->toArray());
  }

  public function getHitShotList(): LocationList {
    return new LocationList($this->hitShotList->toArray());
  }
}