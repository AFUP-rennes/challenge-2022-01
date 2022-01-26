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

class Writer
{
    private IO $io;

    public function __construct(IO $io)
    {
        $this->io = $io;
    }

    public function won(): void
    {
        $this->io->write(State::WON_LABEL);
    }

    public function hit(): void
    {
        $this->io->write(State::HIT_LABEL);
    }

    public function miss(): void
    {
        $this->io->write(State::MISS_LABEL);
    }

    public function sunk(): void
    {
        $this->io->write(State::SUNK_LABEL);
    }

    public function acknowledge(): void
    {
        $this->io->write(State::OK_LABEL);
    }

    public function coordinates(Coordinates $coordinates): void
    {
        $this->io->write((string) $coordinates);
    }

    public function deny(string $message): void
    {
        $this->io->write("error [$message]");
    }
}
