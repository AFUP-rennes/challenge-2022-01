<?php
declare(strict_types=1);

namespace Battle;

final class Boat
{
    public const STATE_OK = 'ok';
    public const STATE_HIT = 'hit';
    public const STATE_SUNK = 'sunk';

    /**
     * @var array<string, string> $coordinates
     */
    public array $coordinates;
    public bool $ready = false;

    public function __construct(
        public int $length
    ){}

    /**
     * @param array<int, string> $coordinates
     */
    public function setCoordinates(array $coordinates): void
    {
        foreach ($coordinates as $c) {
            $this->coordinates[$c] = self::STATE_OK;
        }
    }

    public function check(string $coordinate): string
    {
        foreach ($this->coordinates as $c => $state) {
            if ($c === $coordinate) {
                $this->coordinates[$coordinate] = self::STATE_HIT;

                // Coulé ? ou Touché ?
                return $this->sinked() ? self::STATE_SUNK : self::STATE_HIT;
            }
        }

        return self::STATE_OK;
    }

    public function sinked(): bool
    {
        return !in_array(self::STATE_OK, $this->coordinates);
    }
}