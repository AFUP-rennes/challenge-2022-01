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

use Countable;
use Iterator;
use Ouebsson\BattleShip\Location;

interface LocationListInterface extends Iterator, Countable {

  /**
   * Add a copy of location in the list.
   *
   * @param Location $location
   * @return bool true on success
   */
  public function append(Location $location): bool;

  /**
   * Add a copy of locations list in the list.
   *
   * @param LocationListInterface $locationList
   * @return bool true on success
   */
  public function appendList(LocationListInterface $locationList): bool;

  /**
   * Return true if the location exist in the list.
   *
   * @param Location $location
   * @return bool
   */
  public function isset(Location $location): bool;

  /**
   * Return true if all the locations in the list are valid.
   * Otherwise returns false, as well as if the list is empty.
   *
   * @return bool
   */
  public function isValid(): bool;

  /**
   * Returns true if no location in the given list is common to our list.
   * Returns false if at least 1 location is common or if the 2 lists are empty.
   *
   * @param LocationListInterface $locationList
   * @return bool
   */
  public function xorLocationList(LocationListInterface $locationList): bool;

  /**
   * Return a copy of the list as array.
   *
   * @return array
   */
  public function toArray(): array;

  /**
   * Returns the list of locations and all adjacent locations of
   * the totality of locations as if they were a border of 1 wide.
   *
   * @return LocationListInterface
   */
  public function getBorderedList(): LocationListInterface;
}