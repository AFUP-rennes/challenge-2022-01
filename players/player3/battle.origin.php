<?php
declare(strict_types=1);

$count = 5;
while (true) {
    $command = fgets(STDIN);
    if ($command === false) {
        die('error could not read STDIN');
    }
    $command = trim($command);
    if ($command === 'your turn') {
        echo chr(mt_rand(65, 74)), mt_rand(1,10), "\n";
    } elseif (preg_match('`^([A-J](?:[1-9]|10))$`i', $command)) {
        if ($count-- === 0) {
            echo "won\n";
        } else {
            echo mt_rand(0, 3) === 0 ? "hit\n" : "miss\n";
        }
    } elseif (preg_match('`^hit|miss|sunk$`i', $command)) {
        echo "ok\n";
    } elseif ($command === 'won') {
        echo "ok\n";
        break;
    } else {
        die("error Can't understand '$command'\n");
    }
}