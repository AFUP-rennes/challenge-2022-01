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

/**
 * The AI checker will check coordinate request from opponent and respond with appropriate state: hit, miss, sunk or won.
 */
interface AICheckerInterface
{
    /**
     * Initialize Checker
     */
    public function init(): void;

    /**
     * Get state of our board at the given coordinates
     *
     * @param Coordinates $coordinates
     * @return int
     */
    public function state(Coordinates $coordinates): int;

    /**
     * Get Internal board.
     *
     * @return Board
     */
    public function getBoard(): Board;
}
