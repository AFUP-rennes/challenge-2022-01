<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Service;

class Randomizer
{
    /**
     * Randomize an associated array by keeping association between keys & values
     *
     * @param array $array
     * @return array
     */
    public function randomize(array $array): array
    {
        $new  = [];
        $keys = array_keys($array);

        shuffle($keys);

        foreach($keys as $key) {
            $new[$key] = $array[$key];
        }

        return $new;
    }
}
