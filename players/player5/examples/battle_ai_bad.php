<?php

declare(strict_types=1);

namespace Application;

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

//~ AI
$placer   = new AI\Placer\InvalidAIPlacer();
$checker  = new AI\Checker\BasicAIChecker($placer);
$analyzer = new AI\Analyzer\RandomAIAnalyzer(new Randomizer());

//~ Game
(new Game($player, $reader, $writer, $checker, $analyzer, $logger))->run();
