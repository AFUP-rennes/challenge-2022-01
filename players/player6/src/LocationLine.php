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

use Iterator;
use Ouebsson\BattleShip\Contract\LocationListInterface;

class LocationLine implements LocationListInterface {

  private LocationList $list;

  public function __construct(Location $location, int $length, string $cardinalDirection) {
    $this->list = new LocationList([$location]);
    $length     = min(10, max(0, $length));
    while($this->list->count() < $length) {
      $newLocation = $location->next($cardinalDirection);
      if($newLocation) {
        $location = $newLocation;
        $this->list->append($location);
      }else{
        $cardinalDirection = CardinalDirection::reverse($cardinalDirection);
      }
    }
  }

  public static function fromIterator(Iterator $list): self {
    $locationLine = new self($list->rewind(), 0, CardinalDirection::NORTH);
    foreach($list as $location) {
      $locationLine->append($location);
    }
    return $locationLine;
  }

  /**
   * @inheritDoc
   */
  public function rewind(): Location|false {
    return $this->list->rewind();
  }

  /**
   *
   *
   * @param Location $location
   * @return bool
   */
  public function append(Location $location): bool {
    return $this->list->append($location);
  }

  /**
   * @inheritDoc
   */
  public function current(): Location|false {
    return $this->list->current();
  }

  /**
   * @inheritDoc
   */
  public function next(): Location|false {
    return $this->list->next();
  }

  /**
   * @inheritDoc
   */
  public function key(): string {
    return $this->list->key();
  }

  /**
   * @inheritDoc
   */
  public function valid(): bool {
    return $this->list->valid();
  }

  /**
   * @inheritDoc
   */
  public function count(): int {
    return $this->list->count();
  }

  /**
   * @inheritDoc
   */
  public function isset(Location $location): bool {
    return $this->list->isset($location);
  }

  /**
   * @inheritDoc
   */
  public function toArray(): array {
    return $this->list->toArray();
  }

  /**
   * Return true if all locations in the list are valid and
   * aligned on the same horizontal or vertical axis.
   * Otherwise returns false, as well as if the list is empty.
   *
   * @return bool
   */
  public function isValid(): bool {
    if(!$this->list->isValid()) {
      return false;
    }
    $sumX = 0;
    $sumY = 0;
    foreach($this as $location) {
      $sumX |= $location->getX();
      $sumY |= $location->getY();
    }
    $firstLoc = $this->rewind();
    return ($firstLoc->getX() & $sumX) === $sumX
      || ($firstLoc->getY() & $sumY) === $sumY;
  }

  /**
   * @inheritDoc
   */
  public function xorLocationList(LocationListInterface $locationList): bool {
    return $this->list->xorLocationList($locationList);
  }

  /**
   * @inheritDoc
   */
  public function appendList(LocationListInterface $locationList): bool {
    return $this->list->appendList($locationList);
  }

  /**
   * @inheritdoc
   */
  public function getBorderedList(): LocationList {
    return $this->list->getBorderedList();
  }
}