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
use Ouebsson\BattleShip\CardinalDirection;
use Ouebsson\BattleShip\Game;
use Ouebsson\BattleShip\HiddenShip;
use Ouebsson\BattleShip\Location;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase {

  public function testNewInstance(): void {
    $this->assertInstanceOf(
      Game::class,
      new Game()
    );
    $this->assertEmpty((new Game())->start());
  }

  public function testHuntModFalseThenRotateThreeTimesThenHit(): void {
    $game = new Game();
    $game->getOther()->addShip(new HiddenShip());

    $this->assertFalse($game->getHuntMod());
    $this->assertEquals("F6", $game->getMyNextShotCenterGrid());
    $this->assertFalse($game->getHuntMod());

    $this->assertTrue($game->setLastShotResult(BattleShip::L_HIT));

    $this->assertEquals("F5", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::NORTH, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_MISS));

    $this->assertEquals("G6", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::EAST, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_MISS));

    $this->assertEquals("F7", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::SOUTH, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_MISS));

    $this->assertEquals("E6", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::WEST, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_HIT));
    $this->assertEquals(CardinalDirection::WEST, $game->getHuntMod());
  }

  public function testHuntModGoNorthOnHitAndSouthAfterMiss(): void {
    $game = new Game();
    $game->getOther()->addShip(new HiddenShip());

    $nextShot = $game->getMyNextShotCenterGrid();
    $location = Location::fromString($nextShot);
    $this->assertEquals("F6", "$location");

    $this->assertFalse($game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_HIT));

    $this->assertEquals("F5", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::NORTH, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_HIT));

    $this->assertEquals("F4", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::NORTH, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_MISS));

    $this->assertEquals("F7", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::SOUTH, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_HIT));

    $this->assertEquals("F8", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::SOUTH, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_SUNK));
    $this->assertFalse($game->getHuntMod());
    $this->assertTrue($game->getOther()->isWon());
  }

  public function testHuntOnRightBorder(): void {
    $game = new Game();
    $game->getOther()->addShip(new HiddenShip());

    $this->assertEquals("J6", $game->forceMyNextShotToLocation(Location::fromString("J6")));

    $this->assertFalse($game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_HIT));

    $this->assertEquals("J5", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::NORTH, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_MISS));

    $this->assertEquals("J7", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::SOUTH, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_MISS));

    $this->assertEquals("I6", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::WEST, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_HIT));

    $this->assertEquals("H6", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::WEST, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_HIT));

    $this->assertEquals("G6", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::WEST, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_SUNK));
    $this->assertFalse($game->getHuntMod());

    $this->assertTrue($game->getOther()->isWon());
  }

  public function testHuntOnBottomBorder(): void {
    $game = new Game();
    $game->getOther()->addShip(new HiddenShip());

    $this->assertEquals("E10", $game->forceMyNextShotToLocation(Location::fromString("E10")));

    $this->assertFalse($game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_HIT));

    $this->assertEquals("E9", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::NORTH, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_MISS));

    $this->assertEquals("F10", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::EAST, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_MISS));

    $this->assertEquals("D10", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::WEST, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_HIT));

    $this->assertEquals("C10", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::WEST, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_HIT));

    $this->assertEquals("B10", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::WEST, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_SUNK));
    $this->assertFalse($game->getHuntMod());

    $this->assertTrue($game->getOther()->isWon());
  }

  public function testHuntOnLeftBorder(): void {
    $game = new Game();
    $game->getOther()->addShip(new HiddenShip());

    $this->assertEquals("A2", $game->forceMyNextShotToLocation(Location::fromString("A2")));

    $this->assertFalse($game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_HIT));

    $this->assertEquals("A1", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::NORTH, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_HIT));

    $this->assertEquals("A3", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::SOUTH, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_HIT));

    $this->assertEquals("A4", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::SOUTH, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_SUNK));
    $this->assertFalse($game->getHuntMod());

    $this->assertTrue($game->getOther()->isWon());
  }

  public function testHuntWithMissOnSouth(): void {
    $game = new Game();
    $game->getOther()->addShip(new HiddenShip());

    foreach([
              "H5"  => BattleShip::L_MISS,
              "I4"  => BattleShip::L_MISS,
              "E2"  => BattleShip::L_MISS,
              "D1"  => BattleShip::L_MISS,
              "H1"  => BattleShip::L_MISS,
              "C10" => BattleShip::L_MISS,
              "C6"  => BattleShip::L_HIT,
              "C5"  => BattleShip::L_MISS,
              "D6"  => BattleShip::L_HIT,
              "E6"  => BattleShip::L_HIT,
              "F6"  => BattleShip::L_MISS,
              "B6"  => BattleShip::L_HIT,
              "A6"  => BattleShip::L_SUNK,
              "G8"  => BattleShip::L_HIT,
              "G7"  => BattleShip::L_MISS,
              "H8"  => BattleShip::L_HIT,
              "I8"  => BattleShip::L_HIT,
              "J8"  => BattleShip::L_SUNK,
            ] as $shot => $result) {
      $game->forceMyNextShotToLocation(Location::fromString($shot));
      $game->setLastShotResult($result);
    }

    $this->assertFalse($game->getHuntMod());

    $this->assertEquals("E4", $game->forceMyNextShotToLocation(Location::fromString("E4")));
    $this->assertFalse($game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_HIT));

    $this->assertEquals("E3", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::NORTH, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_MISS));

    $this->assertEquals("F4", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::EAST, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_MISS));

    # Do not go to E5 because MISS!

    $this->assertEquals("D4", $game->getMyNextShot());
    $this->assertEquals(CardinalDirection::WEST, $game->getHuntMod());
    $this->assertTrue($game->setLastShotResult(BattleShip::L_HIT));
    $this->assertEquals(CardinalDirection::WEST, $game->getHuntMod());
  }

}