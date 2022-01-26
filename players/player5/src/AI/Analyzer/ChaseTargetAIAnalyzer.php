<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\AI\Analyzer;

use Application\AI\Bias;
use Application\Board\Board;
use Application\Board\Coordinates;
use Application\Board\Ship;
use Application\Board\State;
use Application\Exception\InvalidBoardStateException;
use Application\Service\Randomizer;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * This class try to chase (at semi random position) and when an opponent ship is hit, switch to target mode.
 * When target mode is enabled, this AI continues to search of other part of the ship until the sunk was sunk (or an
 * error from opponent: invalid size ship, invalid placement or duplicate ship)
 *
 * - Logger is used when debug by using PlayerLogger
 * - Randomizer is used to randomize next cell to target (by keeper array assoc key => value)
 *
 * Bias Type:
 *  The system can use "bias" by checking only on even or odd cell (Bias::EVEN & Bias::ODD). To avoid that, we can use
 *  Bias::NONE to randomize the grid chase cases for a game.
 */
class ChaseTargetAIAnalyzer implements AIAnalyzerInterface
{
    use LoggerAwareTrait;

    private const MODE_CHASE  = 'chase';
    private const MODE_TARGET_ANY = 'target:any';
    private const MODE_TARGET_VERTICAL = 'target:v';
    private const MODE_TARGET_HORIZONTAL = 'target:h';

    private Board $board;
    private Randomizer $randomizer;
    private ?Coordinates $lastCoordinates = null;
    private ?Ship\Unknown $currentTarget = null;
    private string $mode;
    private int $biasType;

    /** @var Coordinates[] $nextTargets */
    private array $nextTargets;

    /** @var Coordinates[] $nextPlays */
    private array $nextPlays = [];

    /** @var Ship\ShipInterface[] $sunkShips */
    private array $sunkShips = [];

    public function __construct(Randomizer $randomizer, LoggerInterface $logger, int $biasType)
    {
        $this->randomizer = $randomizer;
        $this->biasType   = $biasType;

        $this->board      = new Board(State::UNKNOWN);

        $this->setLogger($logger);
    }

    public function getBoard(): Board
    {
        return $this->board;
    }

    public function init(): self
    {
        //~ Bias system will have grid checking start at A1 to all odd column / line. If opponent take this in account,
        //~ he will limit the ship on odd position. Anti-bias will randomise odd & even cell on each game.
        $gridPosition = $this->biasType === Bias::NONE ? rand(0, 1) : $this->biasType;

        for ($line = 1; $line <= 10; $line++) {
            for ($column = 1; $column <= 10; $column++) {
                if (($column + $line) % 2 !== $gridPosition) {
                    continue;
                }

                $coordinates = new Coordinates($column, $line);
                $this->nextPlays[(string) $coordinates] = $coordinates;
            }
        }

        $this->nextPlays = $this->randomizer->randomize($this->nextPlays);

        $this->initChase();

        return $this;
    }

    public function play(): Coordinates
    {
        return $this->mode === self::MODE_CHASE ? $this->chase() : $this->target();
    }

    public function register(string $state): self
    {
        switch ($state) {
            case State::WON_LABEL:
                return $this;
            case State::HIT_LABEL:
                $this->hit($this->lastCoordinates, $state);
                $this->initTarget();
                break;
            case State::SUNK_LABEL:
                $this->hit($this->lastCoordinates, $state);
                $this->sunk($this->lastCoordinates, $state);
                $this->initTarget();
                $this->initChase();
                break;
            case State::MISS_LABEL:
            default:
                $this->board->miss($this->lastCoordinates);
        }

        if ($this->mode !== self::MODE_CHASE && empty($this->nextTargets)) {
            throw new InvalidBoardStateException('Ship targeted with no more target possible');
        }

        if ($this->mode === self::MODE_CHASE && empty($this->nextPlays)) {
            throw new InvalidBoardStateException('Chase with no more next play');
        }

        return $this;
    }

    private function initChase(): void
    {
        if (!empty($this->nextTargets)) {
            foreach ($this->nextTargets as $coordinates) {
                $this->board->empty($coordinates);
            }
        }

        $this->nextTargets = [];
        $this->mode        = self::MODE_CHASE;
    }

    private function initTarget(): void
    {
        $coordinates = $this->lastCoordinates;
        $directions  = [
            'up'    => $coordinates->getTop(),
            'down'  => $coordinates->getBottom(),
            'right' => $coordinates->getRight(),
            'left'  => $coordinates->getLeft(),
        ];

        if ($this->mode === self::MODE_CHASE) {
            //~ Change mode for any direction + reset list of next targets coordinates
            $this->mode        = self::MODE_TARGET_ANY;
        } elseif ($this->mode === self::MODE_TARGET_VERTICAL) {
            //~ Remove left & rights next targets coordinates in mode target (we want to check up & down only)
            unset($directions['left'], $directions['right']);
        } elseif ($this->mode === self::MODE_TARGET_HORIZONTAL) {
            //~ Remove up & down next targets coordinates in mode target (we want to check right & left only)
            unset($directions['up'], $directions['down']);
        }

        foreach ($directions as $nextCoordinates) {
            if (!$this->board->exists($nextCoordinates)) {
                continue; // Out of bound, so skip it
            }

            $state = $this->board->getState($nextCoordinates);
            if (($state & State::UNKNOWN) !== State::UNKNOWN) {
                continue; // Already known state, so skip it
            }

            $this->nextTargets[(string) $nextCoordinates] = $nextCoordinates;
        }
    }

    private function chase(): Coordinates
    {
        $this->lastCoordinates = array_pop($this->nextPlays) ?? new Coordinates(0, 0);
        $this->logger->info('Try chase at "' . $this->lastCoordinates . '"!');

        return $this->lastCoordinates;
    }

    private function target(): Coordinates
    {
        $this->lastCoordinates = array_pop($this->nextTargets) ?? new Coordinates(0, 0);
        $this->logger->info('Try target (mode: ' . $this->mode . ') at ' . $this->lastCoordinates . '"!');

        //~ Remove current target coordinates from next plays if exists to avoid a check in future
        $pos = (string) $this->lastCoordinates;
        if (isset($this->nextPlays[$pos])) {
            unset($this->nextPlays[$pos]);
        }

        return $this->lastCoordinates;
    }

    private function hit(?Coordinates $coordinates, string $state): void
    {
        if ($coordinates === null) {
            return;
        }

        if ($this->currentTarget === null) {
            $this->currentTarget = new Ship\Unknown();
        }

        $this->currentTarget->discover($coordinates);
        $this->board->hit($coordinates);

        //~ Do not check for validity if opponent said we have won :)
        if ($state === State::WON_LABEL) {
            return;
        }

        $empty = [
            $coordinates->getTopLeft(),
            $coordinates->getTopRight(),
            $coordinates->getBottomLeft(),
            $coordinates->getBottomRight(),
        ];

        foreach ($empty as $emptyCoordinates) {
            $pos = (string) $emptyCoordinates;

            if (!$this->board->exists($emptyCoordinates) ) {
                continue;
            }

            $this->board->empty($emptyCoordinates);
            $this->board->check($emptyCoordinates);

            if (isset($this->nextPlays[$pos])) {
                unset($this->nextPlays[$pos]);
            }
        }

        if ($this->mode === self::MODE_CHASE) {
            return;
        }

        $this->changeTargetMode($coordinates);
    }

    private function sunk(?Coordinates $coordinates, string $state): void
    {
        if (empty($coordinates)) {
            return;
        }

        $this->currentTarget->discover($coordinates);
        $this->board->sunk($coordinates);

        //~ Do not check for validity if opponent said we have won :)
        if ($state === State::WON_LABEL) {
            return;
        }

        if ($this->currentTarget === null) {
            throw new InvalidBoardStateException('A ship cannot have a size of "1"!');
        }

        //~ Add ship to list of opponent sunk ships
        $alreadySunkCruiser = isset($this->sunkShips['Cruiser']);
        $sunkShip           = $this->currentTarget->convert($alreadySunkCruiser);

        if (isset($this->sunkShips[$sunkShip->getName()])) {
            throw new InvalidBoardStateException($sunkShip->getName() . ' already sunk !');
        }

        $this->sunkShips[$sunkShip->getName()] = $sunkShip;

        $this->currentTarget = null;
    }

    private function changeTargetMode(Coordinates $coordinates): void
    {
        $direction = [
            'up'    => $coordinates->getTop(),
            'down'  => $coordinates->getBottom(),
            'right' => $coordinates->getRight(),
            'left'  => $coordinates->getLeft(),
        ];

        //~ Check for specific target orientation
        foreach ($direction as $dir => $coordinates) {
            if (!$this->board->exists($coordinates)) {
                continue; // Out of bound, so skip it
            }

            $state   = $this->board->getState($coordinates);
            $newMode = $dir === 'up' || $dir === 'down' ? self::MODE_TARGET_VERTICAL : self::MODE_TARGET_HORIZONTAL;

            if (($state & State::SHIP) === State::SHIP) {
                $this->mode = $newMode;
                break;
            }
        }
    }
}
