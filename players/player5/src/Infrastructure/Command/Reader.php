<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Infrastructure\Command;

use Application\Board\Coordinates;
use Application\Board\State;
use Application\Infrastructure\System\IO;

class Reader
{
    private ?Coordinates $coordinates = null;
    private IO $io;
    private string $command;
    private string $error = '';

    public function __construct(IO $io)
    {
        $this->io = $io;
    }

    public function read(): self
    {
        $this->command = $this->io->read();

        if (empty($this->command)) {
            $this->error = 'Command could not read from STDIN or is empty!';
            return $this;
        }

        if (preg_match('`^(hit|miss|sunk|won|your turn)$`i', $this->command)) {
            return $this;
        }

        $this->parseCoordinates($this->command);

        return $this;
    }

    public function isHit(): bool
    {
        return $this->command === State::HIT_LABEL;
    }

    public function isNewTurn(): bool
    {
        return $this->command === State::TURN_LABEL;
    }

    public function isMiss(): bool
    {
        return $this->command === State::MISS_LABEL;
    }

    public function isSunk(): bool
    {
        return $this->command === State::SUNK_LABEL;
    }

    public function isWon(): bool
    {
        return $this->command === State::WON_LABEL;
    }

    public function hasError(): bool
    {
        return !empty($this->error);
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function getCoordinates(): ?Coordinates
    {
        return $this->coordinates;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    private function parseCoordinates(string $command): void
    {
        if (! (bool) preg_match('`^([A-J](?:[1-9]|10))$`i', $command, $matches)) {
            $this->error = 'Coordinates are not valid!';
            return;
        }

        $this->coordinates = Coordinates::fromString($matches[0]);
    }

}
