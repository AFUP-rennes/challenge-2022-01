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

namespace Ouebsson\BattleShip\Contract;

use Ouebsson\BattleShip\Location;
use Ouebsson\BattleShip\LocationList;

interface ShipInterface {

  /**
   * Return true if the length is between 2 and 5 inclusive.
   *
   * @return bool
   */
  public function isValid(): bool;

  /**
   * Return the known length of the boat.
   *
   * @return int
   */
  public function getLength(): int;

  /**
   * Try to shoot the ship, return true if the ship is hit
   * and save it for a later calculation determining if the ship sank.
   *
   * @param Location $location
   * @return bool
   */
  public function shot(Location $location): bool;

  /**
   * Return true if the ship is sunk.
   *
   * @return bool
   */
  public function isSunk(): bool;

  /**
   * Return a copy of the hit list.
   *
   * @return LocationListInterface
   */
  public function getHitShots(): LocationListInterface;

  /**
   * Return a copy of the location list.
   *
   * @return LocationListInterface
   */
  public function getLocationList(): LocationListInterface;
}