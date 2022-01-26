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
use Application\Service\Randomizer;

/**
 * The purpose of this "IA" is to just play at random, but without request the same cell twice. Simple but not efficient
 * AI :)
 */
class RandomAIAnalyzer implements AIAnalyzerInterface
{
    private Randomizer $randomizer;
    private Board $board;
    private Coordinates $lastCoordinates;
    private array $nextPlays = [];

    public function __construct(Randomizer $randomizer)
    {
        $this->randomizer = $randomizer;
        $this->board      = new Board(State::UNKNOWN);
    }

    public function getBoard(): Board
    {
        return $this->board;
    }

    public function init(): self
    {
        for ($line = 1; $line <= 10; $line++) {
            for ($column = 1; $column <= 10; $column++) {

                $coordinates = new Coordinates($column, $line);
                $this->nextPlays[(string) $coordinates] = $coordinates;
            }
        }

        $this->nextPlays = $this->randomizer->randomize($this->nextPlays);

        return $this;
    }

    public function play(): Coordinates
    {
        $this->lastCoordinates = array_pop($this->nextPlays) ?? new Coordinates(1, 1);

        return $this->lastCoordinates;
    }

    public function register(string $state): self
    {
        switch ($state) {
            case State::HIT_LABEL:
                $this->board->hit($this->lastCoordinates);
                break;
            case State::WON_LABEL:
            case State::SUNK_LABEL:
                $this->board->sunk($this->lastCoordinates);
                break;
            case State::MISS_LABEL:
            default:
                $this->board->miss($this->lastCoordinates);
        }

        return $this;
    }
}
