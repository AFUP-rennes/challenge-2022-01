<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Player;

class Player
{
    private int $id;
    private int $turn;

    public function __construct(int $id)
    {
        $this->id   = $id;
        $this->turn = 0;
    }

    public function newTurn(): void
    {
        $this->turn++;
    }

    public function getName(): string
    {
        return 'Player #' . $this->id;
    }

    public function getTurn(): int
    {
        return $this->turn;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
