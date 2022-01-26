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

final class Game {

  const CARDINAL_DIRECTIONS = [
    0 => CardinalDirection::NORTH,
    1 => CardinalDirection::EAST,
    2 => CardinalDirection::SOUTH,
    3 => CardinalDirection::WEST,
  ];

  private Grid         $me;
  private Grid         $other;
  private bool         $debug;
  private string       $filename;
  private Location     $lastLocationShoot;
  private false|string $huntMod;
  private Location     $huntFirstLocation;
  private int          $huntHits;
  private int          $trials;

  public function __construct(bool $debug = false) {
    $this->debug    = $debug;
    $this->filename = __DIR__."/../".date("Ymd\THis")."_Ouebsson_BattleShip_pid".getmypid().".log";
    $this->log("New Game");
    $this->lastLocationShoot = new Location(-10, -10);
    $this->disableHunt();
    $this->trials = 0;

    $this->me    = new Grid();
    $this->other = new Grid();
  }

  /**
   * Saves a message in a log with date and time.
   *
   * @param string $message
   */
  private function log(string $message): void {
    if($this->debug) {
      $date = date("Y-m-d\TH:i:s.u");
      file_put_contents($this->filename, "[$date] $message\n", FILE_APPEND);
    }
  }

  private function disableHunt() {
    $this->huntMod           = false;
    $this->huntFirstLocation = new Location(-10, -10);
    $this->huntHits          = 0;
  }

  public function start(): void {
    $this->log("Game start, I'll place my ships");
    $this->placeMyShips();
    $this->placeOtherShips();
    $this->log("me: \n$this->me");
  }

  private function placeMyShips(): void {
    foreach([5, 4, 3, 3, 2] as $length) {
      do {
        $x        = rand(0, 9);
        $y        = rand(0, 9);
        $location = new Location($x, $y);
      }while($this->me->getShipsLocations()->getBorderedList()->isset($location));
      do {
        $line = new LocationLine($location, $length, self::CARDINAL_DIRECTIONS[rand(0, 3)]);
      }while(
        $line->count() !== $length
        || !$line->isValid()
        || !$this->me->getShipsLocations()->getBorderedList()->xorLocationList($line)
      );
      $this->me->addShip(new Ship($line));
    }
  }

  private function placeOtherShips(): void {
    $this->other->addShip(new HiddenShip());
    $this->other->addShip(new HiddenShip());
    $this->other->addShip(new HiddenShip());
    $this->other->addShip(new HiddenShip());
    $this->other->addShip(new HiddenShip());
  }

  public function getMyNextShot(): string {
    $missedList   = $this->other->getMissedShotList();
    $forbidenList = new LocationList();
    $forbidenList->appendList($missedList);
    $forbidenList->appendList($this->other->getHitShotList());

    if($this->huntMod) {
      if($this->huntHits === 1) {
        $locN = $this->huntFirstLocation->next(CardinalDirection::NORTH);
        $locE = $this->huntFirstLocation->next(CardinalDirection::EAST);
        $locS = $this->huntFirstLocation->next(CardinalDirection::SOUTH);
        $locW = $this->huntFirstLocation->next(CardinalDirection::WEST);

        if($locN && !$forbidenList->isset($locN)) {
          $this->huntMod = CardinalDirection::NORTH;
          return $this->saveNextShot($locN);
        }else if($locE && !$forbidenList->isset($locE)) {
          $this->huntMod = CardinalDirection::EAST;
          return $this->saveNextShot($locE);
        }else if($locS && !$forbidenList->isset($locS)) {
          $this->huntMod = CardinalDirection::SOUTH;
          return $this->saveNextShot($locS);
        }
        $this->huntMod = CardinalDirection::WEST;
        return $this->saveNextShot($locW);
      }
      if(!$missedList->isset($this->lastLocationShoot)) {
        $nextLoc = $this->lastLocationShoot->next($this->huntMod);
        if($nextLoc && !$forbidenList->isset($nextLoc)) {
          return $this->saveNextShot($nextLoc);
        }
      }
      $this->huntMod           = CardinalDirection::reverse($this->huntMod);
      $this->lastLocationShoot = $this->huntFirstLocation->clone();
      return $this->getMyNextShot();
    }
    do {
      $x_parity = rand(0, 1);
      $y_parity = $x_parity ? 0 : 1;
      $x        = $x_parity + rand(0, 4) * 2;
      $y        = $y_parity + rand(0, 4) * 2;
      $nextLoc  = new Location($x, $y);
    }while($forbidenList->isset($nextLoc));
    return $this->saveNextShot($nextLoc);
  }

  private function saveNextShot(Location $location): string {
    $this->trials++;
    $this->lastLocationShoot = $location;
    $this->logAllGrid();
    return "$this->lastLocationShoot";
  }

  private function logAllGrid(): void {
    $this->log("Trial : {$this->trials}");
    $g_me    = explode("\n", "$this->me");
    $g_other = explode("\n", "$this->other");
    foreach($g_me as $i => $l_me) {
      $this->log("{$l_me}   {$g_other[$i]}");
    }
  }

  public function getMyNextShotCenterGrid(): string {
    $this->lastLocationShoot = new Location(5, 5);
    return "$this->lastLocationShoot";
  }

  public function forceMyNextShotToLocation(Location $location): string {
    $this->lastLocationShoot = $location->clone();
    return "$this->lastLocationShoot";
  }

  public function setLastShotResult(string $command): bool {
    if(!$this->lastLocationShoot->isValid()) {
      # todo : throw Error ?
      $this->log("ERROR ??? ".__FUNCTION__.":L".__LINE__);
      return false;
    }
    match ($command) {
      BattleShip::L_HIT  => $this->setLastShotHit(),
      BattleShip::L_SUNK => $this->setLastShotSunk(),
      BattleShip::L_MISS => $this->setLastShotMiss(),
    };
    return true;
  }

  private function setLastShotHit(): void {
    $this->other->hit($this->lastLocationShoot);
    $this->huntHits++;
    if(!$this->huntMod) {
      $this->huntMod           = CardinalDirection::NORTH;
      $this->huntFirstLocation = $this->lastLocationShoot->clone();
    }
  }

  private function setLastShotSunk(): void {
    $ship = $this->other->hit($this->lastLocationShoot)->setSunk(true);
    foreach($ship->getLocationList()->getBorderedList() as $location) {
      if(!$ship->getLocationList()->isset($location)) {
        $this->other->miss($location);
      }
    }
    $this->disableHunt();
  }

  private function setLastShotMiss(): void {
    $this->other->miss($this->lastLocationShoot);
  }

  public function evaluateTheOthersShotAtMe(Location $location): string {
    $ship = $this->me->shot($location);
    if($ship instanceof Ship) {
      if($this->me->isWon()) {
        return BattleShip::L_WON;
      }
      return $ship->isSunk() ? BattleShip::L_SUNK : BattleShip::L_HIT;
    }
    $this->me->miss($location);
    return BattleShip::L_MISS;
  }

  /**
   * @return Grid
   */
  public function getMe(): Grid {
    return clone $this->me;
  }

  /**
   * @return Grid
   */
  public function getOther(): Grid {
    return $this->other;
  }

  /**
   * @return Location
   */
  public function getLastLocationShoot(): Location {
    return $this->lastLocationShoot;
  }

  /**
   * @return false|string
   */
  public function getHuntMod(): bool|string {
    return $this->huntMod;
  }
}