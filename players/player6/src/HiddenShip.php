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

use Ouebsson\BattleShip\Contract\ShipInterface;

/**
 * A ship whose location on the grid is unknown.
 * Its locations are only discovered when we manage to touch it.
 * The length will only be known once the ship is sunk.
 */
final class HiddenShip implements ShipInterface {

  private LocationList $hitShots;
  private bool         $sunk;

  public function __construct() {
    $this->sunk     = false;
    $this->hitShots = new LocationList();
  }

  /**
   * @inheritDoc
   */
  public function isValid(): bool {
    return $this->getLength() === Ship::normalizedLength($this->getLength());
  }

  /**
   * @inheritDoc
   */
  public function getLength(): int {
    return $this->hitShots->count();
  }

  /**
   * @inheritDoc
   */
  public function shot(Location $location): bool {
    $this->hitShots->append($location);
    return true;
  }

  /**
   * @inheritDoc
   */
  public function isSunk(): bool {
    return $this->sunk;
  }

  /**
   * Force the sunken state of the ship regardless
   * of the known and touched locations.
   *
   * @param bool $sunk
   * @return HiddenShip
   */
  public function setSunk(bool $sunk): self {
    $this->sunk = $sunk;
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getLocationList(): LocationList {
    return $this->getHitShots();
  }

  /**
   * @inheritDoc
   */
  public function getHitShots(): LocationList {
    return new LocationList($this->hitShots->toArray());
  }
}