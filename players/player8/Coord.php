<?php

class Coord
{
    public static function fromIndex(int $index): string
    {
        $x = $index % Layout::SIZE;
        $y = \intdiv($index, Layout::SIZE);
        $alphabet = \range('A', 'Z');

        return $alphabet[$y].$x + 1;
    }

    public static function toIndex(string $string): int
    {
        $alphabet = \array_flip(\range('A', 'Z'));
        $y = $alphabet[$string[0]];
        $x = (int) \substr($string, 1);

        return ($x + ($y * Layout::SIZE)) -1;
    }
}
