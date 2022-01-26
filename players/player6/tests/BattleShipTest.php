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

use Ouebsson\BattleShip\BattleShip;
use PHPUnit\Framework\TestCase;

class BattleShipTest extends TestCase {

  public function testExpectConstMatchRulesTerms() {
    $this->assertEquals(BattleShip::L_HIT, "hit");
    $this->assertEquals(BattleShip::L_MISS, "miss");
    $this->assertEquals(BattleShip::L_OK, "ok");
    $this->assertEquals(BattleShip::L_SUNK, "sunk");
    $this->assertEquals(BattleShip::L_WON, "won");
    $this->assertEquals(BattleShip::L_YOUR_TURN, "your turn");
  }
}