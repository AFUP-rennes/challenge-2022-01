<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\AI\Checker;

use Application\AI\Placer\AIPlacerInterface;
use Application\Board\Board;
use Application\Board\Coordinates;
use Application\Board\Ship;
use Application\Board\State;

class BasicAIChecker implements AICheckerInterface
{
    private AIPlacerInterface $placer;
    private Board $board;

    /** @var Ship\ShipInterface[] $ships */
    private array $shipsByCoordinates;

    /** @var Ship\ShipInterface[] $ships */
    private array $ships;

    public function __construct(AIPlacerInterface $placer)
    {
        $this->board  = new Board(State::EMPTY);
        $this->placer = $placer;
    }

    public function getBoard(): Board
    {
        return $this->board;
    }

    public function init(): void
    {
        $this->ships = $this->placer->place();

        foreach ($this->ships as $ship) {
            $this->board->addShip($ship);
            foreach ($ship->get() as $coordinates) {
                $this->shipsByCoordinates[(string) $coordinates] = $ship;
            }
        }
    }

    public function state(Coordinates $coordinates): int
    {
        $pos  = (string) $coordinates;
        $ship = $this->shipsByCoordinates[$pos] ?? null;

        if ($ship === null || $ship->isSunk()) {
            return State::MISS;
        }

        //~ Register hit on ship
        $ship->hit($coordinates);

        //~ Register opponent hit on our board
        $this->board->hit($coordinates);

        //~ Check for sunk
        if ($ship->isSunk()) {
            //~ Register our ship sunk on our board
            foreach ($ship->get() as $coordinates) {
                $this->board->sunk($coordinates);
            }

            //~ Check if all of our ships are sunk (aka opponent win)
            if ($this->isWon()) {
                return State::WON;
            }

            return State::SUNK;
        }

        return State::HIT;
    }

    private function isWon(): bool
    {
        $numberOfSunkShip = false;
        foreach ($this->ships as $ship) {
            $numberOfSunkShip += (int) $ship->isSunk();
        }

        return ($numberOfSunkShip === count($this->ships));
    }
}
