<?php

declare(strict_types=1);

namespace Challenge;

interface AlgorythmInterface
{
    public function shoot(): string;

    public function answer(string $reply): void;
}
