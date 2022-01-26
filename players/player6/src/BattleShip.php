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

use Throwable;

final class BattleShip {

  const L_HIT       = "hit";
  const L_MISS      = "miss";
  const L_OK        = "ok";
  const L_SUNK      = "sunk";
  const L_WON       = "won";
  const L_YOUR_TURN = "your turn";

  private Game $game;
  private bool $gameEnd = false;

  public function run(bool $debug = false): void {
    $this->game = new Game($debug);
    $this->game->start();
    $stream = new Stream();
    try {

      do {
        $stream->write(
          $this->processCommand(
            $stream->read()
          )
        );
      }while(!$this->gameEnd);
    }catch(Throwable $e) {
      die('error '.$e->getMessage());
    }
  }

  private function processCommand(string $command): string {
    switch($command) {

      case self::L_YOUR_TURN:
        return $this->game->getMyNextShot();

      case self::L_HIT:
      case self::L_MISS:
      case self::L_SUNK:
        $this->game->setLastShotResult($command);
        return self::L_OK;

      case self::L_WON:
        $this->gameEnd = true;
        return self::L_OK;

      default:
        if(preg_match('#^([A-J](?:[1-9]|10))$#i', $command)) {
          return $this->game->evaluateTheOthersShotAtMe(Location::fromString($command));
        }
    }
    die("error Can't understand '$command'\n");
  }

}
