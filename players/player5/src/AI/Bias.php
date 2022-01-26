<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\AI;

/**
 * When we do an algorithm, we can introduce bias accidentally.
 * For example, an optimize way to analyze opponent grid is to try to check only EVEN (line + col = even) cells, like
 * A1, A3, B2... Why even cell ? Because we generally start at the first case, A1.
 *
 * In my first version, I had this bias. So I removed it.
 * But opponent can have the same bias, and we can configure our AI placer to limit the number of case with ship on it
 * by using the opposite bias (maximize placement on "odd" cells).
 *
 * We can configure AI Analyzer to force using specific bias to compare with another pure randomly placement / analyzing.
 */
final class Bias
{
    public const EVEN = 0;
    public const ODD  = 1;
    public const NONE = 2;
}
