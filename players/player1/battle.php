<?php
declare(strict_types=1);

require 'vendor/autoload.php';

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    (new App\Battle())->run();
} catch (throwable $e) {
    fputs(STDERR, 'error: '.$e->getMessage()."\n");
}
