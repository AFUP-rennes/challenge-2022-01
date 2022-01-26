<?php

declare(strict_types=1);

namespace Challenge;

final class RandAlgorythm implements AlgorythmInterface
{
    private array $coordinates = [];

    public function shoot(): string
    {
        $coordinate = $this->uniqShoot();
        $this->add($coordinate);
        return $coordinate;
    }

    public function uniqShoot(): string
    {
        $coordinate = chr(mt_rand(65, 74)).mt_rand(1, 10);
        while (in_array($coordinate, $this->coordinates)) {
            $coordinate = chr(mt_rand(65, 74)).mt_rand(1, 10);
        }
        return $coordinate;
    }

    public function add(string $coordinate): void
    {
        $this->coordinates[] = $coordinate;
    }

    public function getCoordinates(): array
    {
        return $this->coordinates;
    }

    public function answer(string $reply): void
    {
    }
}
