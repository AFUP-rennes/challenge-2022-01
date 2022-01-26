<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\AI\Analyzer;

use Application\Board\Board;
use Application\Board\Coordinates;
use Application\Board\State;

/**
 * The purpose of this "IA" is to simulate a Mad IA which don't care of placement of the ship, nor verify if coordinates
 * already have been requested.
 */
class MadAIAnalyzer implements AIAnalyzerInterface
{
    private Board $board;

    public function __construct()
    {
        $this->board = new Board(State::UNKNOWN);
    }

    public function getBoard(): Board
    {
        return $this->board;
    }

    public function init(): self
    {
        return $this;
    }

    public function play(): Coordinates
    {
        return new Coordinates(rand(1, 10), rand(1, 10));
    }

    public function register(string $state): self
    {
        return $this;
    }
}
