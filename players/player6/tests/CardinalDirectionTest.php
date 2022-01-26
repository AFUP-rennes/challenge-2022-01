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
use PHPUnit\Framework\TestCase;

class CardinalDirectionTest extends TestCase {

  public function testWording(): void {
    $this->assertEquals("NORTH", CardinalDirection::NORTH);
    $this->assertEquals("EAST", CardinalDirection::EAST);
    $this->assertEquals("SOUTH", CardinalDirection::SOUTH);
    $this->assertEquals("WEST", CardinalDirection::WEST);
  }

  public function testReverse(): void {
    $this->assertEquals(
      CardinalDirection::SOUTH,
      CardinalDirection::reverse(CardinalDirection::NORTH)
    );
    $this->assertEquals(
      CardinalDirection::WEST,
      CardinalDirection::reverse(CardinalDirection::EAST)
    );
    $this->assertEquals(
      CardinalDirection::NORTH,
      CardinalDirection::reverse(CardinalDirection::SOUTH)
    );
    $this->assertEquals(
      CardinalDirection::EAST,
      CardinalDirection::reverse(CardinalDirection::WEST)
    );
  }

  public function testRotate(): void {
    $this->assertEquals(
      CardinalDirection::EAST,
      CardinalDirection::rotate(CardinalDirection::NORTH)
    );
    $this->assertEquals(
      CardinalDirection::SOUTH,
      CardinalDirection::rotate(CardinalDirection::EAST)
    );
    $this->assertEquals(
      CardinalDirection::WEST,
      CardinalDirection::rotate(CardinalDirection::SOUTH)
    );
    $this->assertEquals(
      CardinalDirection::NORTH,
      CardinalDirection::rotate(CardinalDirection::WEST)
    );
  }

}