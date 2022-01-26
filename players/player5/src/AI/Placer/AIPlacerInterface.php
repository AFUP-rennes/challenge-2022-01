<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\AI\Placer;

/**
 * The purpose of this "IA" is to place try to find a correct position for our ships, accordingly to the given strategy.
 */
interface AIPlacerInterface
{
    /**
     * Try to find place of ship on board & return list of ships with origin.
     */
    public function place(): array;
}
