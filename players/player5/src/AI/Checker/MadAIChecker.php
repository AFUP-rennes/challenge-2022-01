<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\AI\Checker;

use Application\Board\Board;
use Application\Board\Coordinates;
use Application\Board\State;

class MadAIChecker implements AICheckerInterface
{
    private Board $board;

    public function __construct()
    {
        $this->board = new Board(State::EMPTY);
    }

    public function getBoard(): Board
    {
        return $this->board;
    }

    public function init(): void
    {
        //~ Do nothing special here
    }

    public function state(Coordinates $coordinates): int
    {
        $states = [State::MISS, State::HIT, State::WON];
        shuffle($states);

        return array_pop($states);
    }
}
