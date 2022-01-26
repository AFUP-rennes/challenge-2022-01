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

class Location {

  /**
   * from [0 => 'A'] to  [9 => 'J']
   *
   * @var int
   */
  private int $x;

  /**
   * from [0 => 1] to  [9 => 10]
   *
   * @var int
   */
  private int $y;

  public function __construct(int $x, int $y) {
    $this->x = $x;
    $this->y = $y;
  }

  public static function fromString(string $literal): self {
    return new self(ord($literal[0]) - 65, ((int) substr($literal, 1)) - 1);
  }

  /**
   * Returns the list of locations adjacent to the present one, naturally
   * excluding it as well as locations that would be out of bounds.
   *
   * @return Generator
   */
  public function getAdjacentLocations(): Generator {
    for($x = -1; $x < 2; $x++) {
      for($y = -1; $y < 2; $y++) {
        if(!($x === 0 && $y === 0)) {
          $coord = new self($this->x + $x, $this->y + $y);
          if($coord->isValid()) {
            yield $coord;
          }
        }
      }
    }
  }

  /**
   * Return true if point is in Grid (x:A->J, y:1->10)
   *
   * @return bool
   */
  public function isValid(): bool {
    return $this->x === min(9, max(0, $this->x))
      && $this->y === min(9, max(0, $this->y));
  }

  public function getX(): int {
    return $this->x;
  }

  public function getY(): int {
    return $this->y;
  }

  public function equals(self $location): bool {
    return $this->x === $location->x && $this->y === $location->y;
  }

  public function clone(): self {
    return new self($this->x, $this->y);
  }

  /**
   * Returns the nearest valid location in the indicated direction, otherwise false.
   *
   * @param string $cardinalDirection
   * @return false|self
   */
  public function next(string $cardinalDirection): self|false {
    $x = $this->x;
    $y = $this->y;

    $location = match ($cardinalDirection) {
      CardinalDirection::NORTH => new Location($x, $y - 1),
      CardinalDirection::EAST  => new Location($x + 1, $y),
      CardinalDirection::SOUTH => new Location($x, $y + 1),
      CardinalDirection::WEST  => new Location($x - 1, $y),
    };

    return $location->isValid() ? $location : false;
  }

  public function __toString(): string {
    return chr($this->x + 65).($this->y + 1);
  }
}