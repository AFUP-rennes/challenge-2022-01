<?php
declare(strict_types=1);

require './vendor/autoload.php';

// https://github.com/AFUP-rennes/challenge-2022-01

use Battle\Tactic;

$player = new Tactic;
$player->init([5, 4, 3, 3, 2]);

while (true) {

    $command = fgets(STDIN);
    if ($command === false) {
        die('error could not read STDIN');
    }

    $command = trim($command);

    // A moi de jouer
    if ($command === Tactic::PLAY_YOUR_TURN) {
        echo $player->play() . "\n";
        continue;
    }

    // On m'attaque
    if (preg_match('`^([A-J](?:[1-9]|10))$`i', $command)) {
        echo $player->checkBoats($command) . "\n";
        continue;
    }

    // Erreur
    if (strpos($command, Tactic::PLAY_ERROR) !== false) {
        echo $command . "\n";
        die('OH !');
    }

    $player->read($command);

    echo Tactic::PLAY_OK . "\n";
}