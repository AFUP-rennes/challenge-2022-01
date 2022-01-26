<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Infrastructure\System;

class IO
{
    private string $date;

    public function __construct(?string $date = null)
    {
        $this->date = $date ?? date('Y-m-d_His');
    }

    public function read(): string
    {
        return trim((string) fgets(STDIN));
    }

    public function write(string $string): void
    {
        fputs(STDOUT, $string . PHP_EOL);
    }

    public function log(string $string): void
    {
        $path = realpath(__DIR__ . '/../../../logs') . '/';
        $file = "$this->date.log";

        file_put_contents($path.$file, $string . PHP_EOL, FILE_APPEND);
    }
}
