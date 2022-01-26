<?php

// @author Eric Jochum
// @link https://github.com/EricJGit

// TODO Next steps
// - Optim scanner
// - Placeur de bateau
// - Detect error adversaire
// - Aller boire une bière

\spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

$timestart = \microtime(true);

$layoutDefence = new Layout();
$defence = new Defence($layoutDefence);

$defenseBoats = [
    // Moyen mais safe (48 tours), exploite A1 et J10
    new Boat([Coord::toIndex('J9'), Coord::toIndex('J10')]),
    new Boat([Coord::toIndex('A1'), Coord::toIndex('B1'), Coord::toIndex('C1')]),
    new Boat([Coord::toIndex('F9'), Coord::toIndex('G9'), Coord::toIndex('H9')]),
    new Boat([Coord::toIndex('C5'), Coord::toIndex('C6'), Coord::toIndex('C7'), Coord::toIndex('C8')]),
    new Boat([Coord::toIndex('H2'), Coord::toIndex('H3'), Coord::toIndex('H4'), Coord::toIndex('H5'), Coord::toIndex('H6')]),

    // Meilleur placement au proba mais risqué car uniquement sur les bords (57 coups en autoaim)
    //new Boat([Coord::toIndex('J9'), Coord::toIndex('J10')]),
    //new Boat([Coord::toIndex('A1'), Coord::toIndex('B1'), Coord::toIndex('C1')]),
    //new Boat([Coord::toIndex('J2'), Coord::toIndex('J3'), Coord::toIndex('J4')]),
    //new Boat([Coord::toIndex('E1'), Coord::toIndex('F1'), Coord::toIndex('G1'), Coord::toIndex('H1')]),
    //new Boat([Coord::toIndex('A6'), Coord::toIndex('A7'), Coord::toIndex('A8'), Coord::toIndex('A9'), Coord::toIndex('A10')]),
];

$defence->addBoats($defenseBoats);

$layoutAttack = new Layout();
$bigBertha = new BigBerthaBattle();
$attack = new Attack($layoutAttack, $bigBertha);

$attackBoats = [
    2,3,3,4,5,
];

$destroyBoats = [];

while (true) {
    $command = fgets(STDIN);
    if ($command === false) {
        die('error could not read STDIN');
    }
    $command = trim($command);
    if ($command === 'your turn') {
        // Attack
        $attack->doAttack();
    } elseif (preg_match('`^([A-J](?:[1-9]|10))$`i', $command)) {
        // Defence
        $res = $defence->handleFire(Coord::toIndex($command));
        echo $res->toString().PHP_EOL;
    } elseif (preg_match('`^hit|miss|sunk$`i', $command)) {
        $attack->handleShootResult(GridIndexKnowledge::$command());
        echo "ok".PHP_EOL;
    } elseif ($command === 'won') {
        echo "ok\n";
        break;
    } else {
        die("error Can't understand '$command'\n");
    }
}
