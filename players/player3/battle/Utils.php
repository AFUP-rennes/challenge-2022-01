<?php
declare(strict_types=1);

namespace Battle;

class Utils
{
    /**
     * @param array<int, string> $array
     */
    public static function array_push_special(array &$array, bool|string $value): void
    {
        if ($value !== false) {
            array_push($array, $value);
        }
    }
}