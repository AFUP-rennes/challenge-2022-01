<?php
declare(strict_types=1);
namespace Challenge;

require 'src/Player.php';
require 'src/Game.php';
require 'src/Challenge.php';

$startTime = time();

$challenge = new Challenge(100, 55, false);
$challenge->start(function(string $player1, string $player2, int $progression) {
    echo "Player {$player1} vs {$player2}: {$progression}%\r";
});

echo "\n\nIt took ", (time()-$startTime), ' seconds to play all games.';
echo "\n\n--------- Result ------------\n";

$errors = [];
foreach ($challenge->getResult() as $position => ['name' => $name, 'won' => $won, 'perf' => $perf, 'errors' => $err]) {
    echo "#{$position}: {$name} - {$won} games won, ";
    echo "{$perf}µs of response time\n";
    if ($err !== []) {
        $errors = array_merge($errors, $err);
    }
}

if ($errors !== []) {
    echo "\n----------- Errors -----------\n";
    foreach ($errors as $error) {
        echo " - {$error}\n";
    }
}