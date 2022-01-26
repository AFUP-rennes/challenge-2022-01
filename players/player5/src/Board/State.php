<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Board;

final class State
{
    public const EMPTY   = 1;
    public const UNKNOWN = 2;
    public const CHECKED = 4;
    public const MISS    = 8;
    public const HIT     = 16;
    public const SUNK    = 32;
    public const SHIP    = 64;
    public const WON     = 1024;

    public const MISS_LABEL = 'miss';
    public const HIT_LABEL  = 'hit';
    public const SUNK_LABEL = 'sunk';

    public const OK_LABEL   = 'ok';
    public const TURN_LABEL = 'your turn';
    public const WON_LABEL  = 'won';
}
