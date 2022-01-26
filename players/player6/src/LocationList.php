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

use Generator;
use loophp\collection\Collection;
use Ouebsson\BattleShip\Contract\LocationListInterface;

final class LocationList implements LocationListInterface {

  /**
   * @var Location[]
   */
  private array $list;
  private array $borderedList;

  /**
   * @param Location[] $list
   */
  public function __construct(array $list = []) {
    $this->list         = [];
    $this->borderedList = [];
    foreach(array_filter($list, fn($value) => $value instanceof Location) as $location) {
      $this->append($location);
    }
  }

  /**
   * @inheritDoc
   */
  public function append(Location $location): bool {
    $this->list["$location"]         = $location->clone();
    $this->borderedList["$location"] = $location->clone();
    foreach($location->getAdjacentLocations() as $adjacentLocation) {
      $this->borderedList["$adjacentLocation"] = $adjacentLocation->clone();
    }
    return $this->isset($location);
  }

  /**
   * @inheritDoc
   */
  public function isset(Location $location): bool {
    return isset($this->list["$location"]);
  }

  public static function fromGenerator(Generator $generator): LocationListInterface {
    return new self(
      Collection::fromGenerator($generator)
                ->all()
    );
  }

  /**
   * @inheritDoc
   */
  public function next(): Location|false {
    next($this->list);
    return $this->current();
  }

  /**
   * @inheritDoc
   */
  public function current(): Location|false {
    return current($this->list);
  }

  /**
   * @inheritDoc
   */
  public function key(): string {
    return key($this->list);
  }

  /**
   * @inheritDoc
   */
  public function valid(): bool {
    return key($this->list) !== null;
  }

  /**
   * @inheritDoc
   */
  public function rewind(): Location|false {
    return reset($this->list);
  }

  /**
   * @inheritDoc
   */
  public function toArray(): array {
    $list = [];
    foreach(Collection::fromIterable($this->list) as $key => $location) {
      $list[$key] = $location->clone();
    }
    return $list;
  }

  /**
   * @inheritDoc
   */
  public function isValid(): bool {
    if(!$this->count()) {
      return false;
    }
    foreach($this->list as $location) {
      if(!$location->isValid()) {
        return false;
      }
    }
    return true;
  }

  /**
   * @inheritDoc
   */
  public function count(): int {
    return count($this->list);
  }

  /**
   * @inheritDoc
   */
  public function xorLocationList(LocationListInterface $locationList): bool {
    if(!$locationList->count() && !$this->count()) {
      return false;
    }
    foreach($this->list as $location) {
      if($locationList->isset($location)) {
        return false;
      }
    }
    return true;
  }

  /**
   * @inheritDoc
   */
  public function appendList(LocationListInterface $locationList): bool {
    $success = true;
    foreach($locationList as $location) {
      $success = $success && $this->append($location);
    }
    return $success;
  }

  /**
   * @inheritdoc
   */
  public function getBorderedList(): self {
    return new self($this->borderedList);
  }
}