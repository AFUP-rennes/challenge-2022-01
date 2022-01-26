<?php
declare(strict_types=1);

/**
 * @method static self OK()
 * @method static self ERROR()
 */
class Response extends Action
{
    private const HIT = 'hit';
    private const SUNK = 'sunk';
    private const MISS = 'miss';
    private const WON = 'won';
    private const COORDINATES = '';
    private const OK = 'ok';
    private const ERROR = '';

    public function send(): void
    {
        echo sprintf("%s\n", $this->value);
    }

    public static function from($value): Response
    {
        if (preg_match('`^([0-9][0-9])$`i', $value)) {
            return Response::fromCoordinates($value);
        } else {
            return parent::from($value);
        }
    }

    public static function numToAlpha(string $value): string
    {
        [$c, $l] = str_split($value);
        return sprintf(
            "%s%s",
            chr(65 + (int)$c),
            ((int)$l) + 1
        );
    }

    public static function fromCoordinates(string $value): static
    {
        $self = Response::COORDINATES();
        $self->value = self::numToAlpha($value);
        return $self;
    }

    public static function fromError(string $value): static
    {
        $self = Response::ERROR();
        $self->value = sprintf("error %s", $value);
        return $self;
    }
}