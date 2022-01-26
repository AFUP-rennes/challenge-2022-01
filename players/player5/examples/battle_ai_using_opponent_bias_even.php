<?php

declare(strict_types=1);

namespace Application;

use Application\AI\Bias;
use Application\Game\Game;
use Application\Infrastructure\Command;
use Application\Infrastructure\System\IO;
use Application\Player\Player;
use Application\Service\Randomizer;
use Psr\Log\NullLogger;

require_once __DIR__ . '/../vendor/autoload.php';

//~ Common Services / Entities
$io     = new IO();          // Input/Output wrapper
$player = new Player(2); // Used for logs
$logger = new NullLogger();  // new PlayerLogger($player, $io); // Enable logger

//~ Command Reader/Writer
$reader = new Command\Reader($io);
$writer = new Command\Writer($io);

//~ AI Config / Bias
$onlyAtBorder     = true;       // Better because statically minor chance to hit on border
$biasTypePlacer   = Bias::ODD; // Specify if we place ship by minimizing position on given bias type
$biasTypeAnalyzer = Bias::NONE; // Specify if analyze by default on given "even" or "odd" grid cell.
                                // When "none", il will be randomly fixed for the rest of the game

//~ AI
$placer   = new AI\Placer\RandomAIPlacer($onlyAtBorder, $biasTypePlacer);
$checker  = new AI\Checker\BasicAIChecker($placer);
$analyzer = new AI\Analyzer\ChaseTargetAIAnalyzer(new Randomizer(), $logger, $biasTypeAnalyzer);

//~ Game
(new Game($player, $reader, $writer, $checker, $analyzer, $logger))->run();
