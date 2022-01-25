<?php

declare(strict_types=1);

namespace Challenge;

require 'src/Player.php';

$players = [];
foreach (glob(__DIR__ . '/players/*') as $file) {
    if (file_exists($file . '/battle.php') && preg_match('`/_[^/]+`', $file) === 0) {
        $players[] = $file . '/battle.php';
    }
}

if ($players === []) {
    die("No player found\n");
}

function test(array $players, string $what, string ...$request)
{
    echo "\n\e[7m#### {$what} ############\e[27m\n";
    foreach ($players as $script) {
        $name = \basename(\dirname($script));
        $player = new Player($name, $script);
        echo "{$name}: ";
        try {
            $sep = '';
            foreach ($request as $req) {
                echo $sep, $player->request($req);
                $sep = ' > ';
            }
        } catch (\Throwable $exception) {
            echo $exception->getMessage();
        }

        echo "\n";
    }
}

test($players, 'Response to "hello"', 'hello');
test($players, 'Response to a hit without asking for coordinate', 'hit');
test($players, 'Response to wrong position "K3"', 'K3');
test($players, 'Response to wrong position "B11"', 'B11');

$success = [];
$count = 10;
if (isset($argv[1]) && $argv[1] === '--grid') {
    $count = 1;
}

while ($count--) {
    foreach ($players as $script) {
        $name = \basename(\dirname($script));
        $success[$name]['success'] ??= 0;
        $player = new Player($name, $script);

        $gridOutput = "  ABCDEFGHIJ\n";
        $error = null;
        $grid = [];
        $shipsSunk = 0;
        try {
            $won = false;
            for ($y = 0; $y <= 9; $y++) {
                $gridOutput .= ($y + 1);
                if ($y < 9) {
                    $gridOutput .= ' ';
                }
                for ($x = 0; $x <= 9; $x++) {
                    if ($won) {
                        $grid[$x][$y] = null;
                        $gridOutput .= '.';
                        continue;
                    }

//                    $player->request('your turn');
//                    $ok = $player->request('miss');
//                    if ($ok !== 'ok') {
//                        throw new \Exception('Fail: did not response ok to "miss": ' . $ok);
//                    }
                    $state = $player->request(chr($x + ord('A')) . ($y + 1));
                    if ($state === 'miss') {
                        $grid[$x][$y] = null;
                        $gridOutput .= '.';
                    } elseif ($state === 'hit') {
                        $grid[$x][$y] = 0;
                        $gridOutput .= 'x';
                    } elseif ($state === 'sunk') {
                        $grid[$x][$y] = 0;
                        $shipsSunk++;
                        $gridOutput .= 'x';
                    } elseif ($state === 'won') {
                        $grid[$x][$y] = 0;
                        $gridOutput .= "x";
                        $shipsSunk++;
                        $won = true;
                    } else {
                        throw new \Exception('Fail: request return : ' . $state);
                    }
                }
                $gridOutput .= "\n";
            }
            $player->stop();

            $ships = [];
            for ($y = 0; $y <= 9; $y++) {
                for ($x = 0; $x <= 9; $x++) {
                    if (($grid[$x][$y] ?? null) === null) {
                        continue;
                    }
                    $l = ($grid[$x - 1][$y] ?? null);
                    $u = ($grid[$x][$y - 1] ?? null);
                    if ($l === null && $u === null) {
                        $ships[] = 1;
                        $grid[$x][$y] = count($ships);
                        continue;
                    }
                    if ($l !== null && $u !== null) {
                        throw new \Exception('Fail: ships are too close at position ' . chr($x + ord('A')) . ($y + 1));
                    }

                    if ($l !== null) {
                        $grid[$x][$y] = $l;
                    } else {
                        $grid[$x][$y] = $u;
                    }
                    $ships[$grid[$x][$y] - 1]++;
                }
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }

        if ($error === null) {
            sort($ships);
            if ($ships !== [2, 3, 3, 4, 5]) {
                $error = 'Fail: Wrong ships distribution: ' . implode(' ', $ships);
            } elseif ($won === false) {
                $error = 'Fail: did not answer "won" at the end';
            } elseif ($shipsSunk !== 5) {
                $error = 'Fail: did not answer "sunk" after each sunken ship. Received: ' . ($shipsSunk - 1);
            } else {
                $success[$name]['success']++;
            }
        }
        if ($error !== null) {
            $success[$name]['error'][] = $error;
        }
        $success[$name]['grid'][] = $gridOutput;
    }
}

echo "\n\e[7m#### Hit the whole grid to check ship positioning ########\e[27m\n";
foreach ($success as $name => $stats) {
    echo $name, ": ";
    if (!isset($stats['error'])) {
        echo "\e[42mOK\e[0m\n";
    } else {
        echo "\e[41mFail\e[0m:\n";
        foreach (array_unique($stats['error']) as $error) {
            echo "\t- {$error}\n";
        }
    }
    if (isset($argv[1]) && $argv[1] === '--grid') {
        echo $stats['grid'][0], "\n";
    }
}
