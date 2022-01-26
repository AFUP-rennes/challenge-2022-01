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

final class CardinalDirection {

  const NORTH = "NORTH";
  const EAST  = "EAST";
  const SOUTH = "SOUTH";
  const WEST  = "WEST";

  public static function reverse(string $cardinalDirection): string {
    return match ($cardinalDirection) {
      CardinalDirection::NORTH => CardinalDirection::SOUTH,
      CardinalDirection::EAST  => CardinalDirection::WEST,
      CardinalDirection::SOUTH => CardinalDirection::NORTH,
      CardinalDirection::WEST  => CardinalDirection::EAST,
    };
  }

  public static function rotate(string $cardinalDirection): string {
    return match ($cardinalDirection) {
      CardinalDirection::NORTH => CardinalDirection::EAST,
      CardinalDirection::EAST  => CardinalDirection::SOUTH,
      CardinalDirection::SOUTH => CardinalDirection::WEST,
      CardinalDirection::WEST  => CardinalDirection::NORTH,
    };
  }
}