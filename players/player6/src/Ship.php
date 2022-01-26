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

use Ouebsson\BattleShip\Contract\LocationListInterface;
use Ouebsson\BattleShip\Contract\ShipInterface;

/**
 * A ship whose precise location on the grid is known.
 */
final class Ship implements ShipInterface {

  private LocationLine $locationLine;
  private LocationList $hitShots;

  /**
   * @param LocationLine $locationLine
   */
  public function __construct(LocationListInterface $locationLine) {
    $this->locationLine = LocationLine::fromIterator($locationLine);
    $this->hitShots     = new LocationList();
  }

  /**
   * @inheritDoc
   */
  public function isValid(): bool {
    return $this->getLength() === self::normalizedLength($this->getLength());
  }

  /**
   * @inheritDoc
   */
  public function getLength(): int {
    return $this->locationLine->count();
  }

  /**
   * Normalize the length between 2 and 5 inclusive.
   *
   * @param int $length
   * @return int
   */
  public static function normalizedLength(int $length): int {
    return min(5, max(2, $length));
  }

  /**
   * @inheritDoc
   */
  public function shot(Location $location): bool {
    if($this->locationLine->isset($location)) {
      $this->hitShots->append($location);
      return true;
    }
    return false;
  }

  /**
   * @inheritDoc
   */
  public function isSunk(): bool {
    return $this->getLength() === $this->hitShots->count();
  }

  /**
   * @inheritDoc
   */
  public function getHitShots(): LocationListInterface {
    return new LocationList($this->hitShots->toArray());
  }

  /**
   * @inheritDoc
   */
  public function getLocationList(): LocationListInterface {
    return LocationLine::fromIterator($this->locationLine);
  }

}