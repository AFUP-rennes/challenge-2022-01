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

interface AIAnalyzerInterface
{
    /**
     * Initialize the analyzer.
     * Mainly prepare the next coordinates to call.
     *
     * @return $this
     */
    public function init(): self;

    /**
     * Analyze & get the coordinates to "play".
     *
     * @return Coordinates
     */
    public function play(): Coordinates;

    /**
     * Register state from opponent response
     *
     * @param string $state
     * @return $this
     */
    public function register(string $state): self;

    /**
     * Get Internal board.
     *
     * @return Board
     */
    public function getBoard(): Board;
}
