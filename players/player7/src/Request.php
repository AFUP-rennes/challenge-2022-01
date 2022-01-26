<?php
declare(strict_types=1);

/**
 * @method static self YOUR_TURN()
 */
class Request extends Action
{
    private const HIT = 'hit';
    private const SUNK = 'sunk';
    private const MISS = 'miss';
    private const WON = 'won';
    private const COORDINATES = '';
    private const YOUR_TURN = 'your turn';

    public static function from($value): self
    {
        if(preg_match('`^([A-J](?:[1-9]|10))$`i', $value)){
            return Request::fromCoordinates($value);
        } else {
            return parent::from($value);
        }
    }

    public static function alphaToNum(string $value): string {
        sscanf($value, "%c%s", $c, $l);
        return sprintf(
            "%s%s",
            ord($c)-65,
            ((int)$l)-1
        );
    }

    public static function fromCoordinates(string $value): static
    {
        $self = Request::COORDINATES();
        $self->value = self::alphaToNum($value);
        return $self;
    }
}