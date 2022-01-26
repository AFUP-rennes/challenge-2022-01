<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Warship\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

$logger = new Logger('game');
$formatter = new LineFormatter(
    null, // Format of message in log, default [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n
    null, // Datetime format
    true, // allowInlineLineBreaks option, default false
    true  // discard empty Square brackets in the end, default false
);

// Debug level handler
$logFilePath = __DIR__.'/logs/client-'.md5(uniqid()).'.log';
$debugHandler = new StreamHandler($logFilePath, Logger::DEBUG);
$debugHandler->setFormatter($formatter);

$logger->pushHandler($debugHandler);

$client = new Client($logger);
$client->setup();

while (true) {
    $command = trim(fgets(STDIN));
    echo $client->handleCommand($command) . "\n";
}