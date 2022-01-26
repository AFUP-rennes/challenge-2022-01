<?php

declare(strict_types=1);

require './vendor/autoload.php';

use Challenge\GameFactory;

$factory = new GameFactory();
$game = $factory->loadFromGenerated();

while (true) {
    $command = fgets(STDIN);
    if ($command === false) {
        die('error could not read STDIN');
    }
    $command = trim($command);
    if ($command === 'your turn') {
        $game->shoot();
    } elseif (preg_match('`^([A-J](?:[1-9]|10))$`i', $command)) {
        $game->play($command);
    } elseif (preg_match('`^hit|miss|sunk$`i', $command)) {
        $game->answer($command);
        echo "ok\n";
    } elseif ($command === 'won') {
        echo "ok\n";
        break;
    } else {
        die("error Can't understand '$command'\n");
    }
}
