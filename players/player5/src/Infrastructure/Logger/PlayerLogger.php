<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Infrastructure\Logger;

use Application\Infrastructure\System\IO;
use Application\Player\Player;
use Psr\Log\LoggerInterface;

class PlayerLogger implements LoggerInterface
{
    private Player $player;
    private IO $io;

    public function __construct(Player $player, IO $io)
    {
        $this->player = $player;
        $this->io     = $io;
    }

    public function emergency($message, array $context = [])
    {
        $this->log('emergency', $message, $context);
    }

    public function alert($message, array $context = [])
    {
        $this->log('alert', $message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->log('critical', $message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->log('error', $message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->log('warning', $message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->log('notice', $message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->log('info', $message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->log('debug', $message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        $level  = strtoupper($level);
        $player = $this->player->getName();
        $log    = "[$level] $player: $message" . (!empty($context) ? ' | ' . json_encode($context) : '');

        $this->io->log($log);
    }
}
