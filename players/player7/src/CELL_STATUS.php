<?php
declare(strict_types=1);

use MyCLabs\Enum\Enum;

/**
 * @method static self Unknown()
 * @method static self Empty()
 * @method static self Full()
 */
class CELL_STATUS extends Enum
{
    private const Unknown = "UNKNOWN";
    private const Empty = "EMPTY";
    private const Full = "FULL";
}