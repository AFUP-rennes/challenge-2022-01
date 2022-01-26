<?php
declare(strict_types=1);

use MyCLabs\Enum\Enum;

/**
 * @method static static HIT()
 * @method static static SUNK()
 * @method static static MISS()
 * @method static static WON()
 * @method static static COORDINATES()
 */
abstract class Action extends Enum
{
    private const HIT = 'hit';
    private const SUNK = 'sunk';
    private const MISS = 'miss';
    private const WON = 'won';
    private const COORDINATES = '';

    abstract static function fromCoordinates(string $value): static;
}