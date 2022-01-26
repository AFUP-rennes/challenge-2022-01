<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Game;

use Application\AI\Analyzer\AIAnalyzerInterface;
use Application\AI\Checker\AICheckerInterface;
use Application\Board;
use Application\Exception\InvalidBoardStateException;
use Application\Exception\InvalidShipSizeException;
use Application\Infrastructure\Command;
use Application\Player\Player;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Throwable;

final class Game
{
    use LoggerAwareTrait;

    private Player $player;
    private Command\Reader $commandReader;
    private Command\Writer $commandWriter;
    private AICheckerInterface $checker;
    private AIAnalyzerInterface $analyzer;

    public function __construct(
        Player $player,
        Command\Reader $commandReader,
        Command\Writer $commandWriter,
        AICheckerInterface $checker,
        AIAnalyzerInterface $artificialIntelligence,
        LoggerInterface $logger
    ) {
        $this->player        = $player;
        $this->commandReader = $commandReader;
        $this->commandWriter = $commandWriter;
        $this->checker       = $checker;
        $this->analyzer      = $artificialIntelligence;

        //~ Save logger
        $this->setLogger($logger);

        //~ Register handler to log errors for debugging
        $this->registerErrorHandler();
    }

    public function run(): void
    {
        $this->checker->init();
        $this->analyzer->init();

        while (true) {
            $this->commandReader->read();

            //~ Check for error
            if ($this->commandReader->hasError()) {
                $this->logger->info('Has error', ['error' => $this->commandReader->getError()]);
                $this->logger->debug((string) $this->checker->getBoard());
                $this->logger->debug((string) $this->analyzer->getBoard());
                $this->commandWriter->deny($this->commandReader->getError());
                break;
            }

            //~ Check for acknowledgement message
            if ($this->commandReader->isHit() || $this->commandReader->isMiss() || $this->commandReader->isSunk()) {
                $this->logger->info('Is Acknowledgement', ['command' => $this->commandReader->getCommand()]);
                try {
                    $this->analyzer->register($this->commandReader->getCommand());
                } catch (InvalidBoardStateException|InvalidShipSizeException $exception) {
                    //~ Handle case when opponent do a mistake from previous response (happening when target mode has no more target)
                    $this->logger->info('Invalid opponent board state detected (reason: ' . $exception->getMessage() . ')');
                    $this->logger->debug((string) $this->analyzer->getBoard());
                    $this->commandWriter->deny('Invalid opponent board state detected (reason: ' . $exception->getMessage() . ')');
                }

                $this->commandWriter->acknowledge();
                continue;
            }

            //~ Check if I won
            if ($this->commandReader->isWon()) {
                $this->analyzer->register(Board\State::WON_LABEL); // Should be a sunk for the last ship
                $this->logger->info('I won (Turn: ' . $this->player->getTurn() . ') !');
                $this->logger->debug((string) $this->checker->getBoard());
                $this->logger->debug((string) $this->analyzer->getBoard());
                $this->commandWriter->acknowledge();
                break;
            }

            //~ Check if it is my turn
            if ($this->commandReader->isNewTurn()) {
                $this->logger->info('New turn');
                $this->player->newTurn();
                $coordinates = $this->analyzer->play();
                $this->logger->info('Analyze & Play at "' . $coordinates . '"');
                $this->commandWriter->coordinates($coordinates);
                continue;
            }

            //~ Otherwise, analyse opponent play & give a response
            $coordinates = $this->commandReader->getCoordinates();
            $this->logger->info('Check for opponent play at "' . $coordinates . '"');

            $state = $this->checker->state($coordinates);
            switch ($state) {
                case Board\State::WON:
                    $this->logger->info('Opponent won !');
                    $this->logger->debug((string) $this->checker->getBoard());
                    $this->logger->debug((string) $this->analyzer->getBoard());
                    $this->commandWriter->won();
                    break;
                case Board\State::SUNK:
                    $this->logger->info('Is sunk !');
                    $this->commandWriter->sunk();
                    break;
                case Board\State::HIT:
                    $this->logger->info('Is hit !');
                    $this->commandWriter->hit();
                    break;
                case Board\State::MISS:
                default:
                    $this->logger->info('Opponent miss !');
                    $this->commandWriter->miss();
            }
        }
    }

    private function registerErrorHandler(): void
    {
        set_error_handler(function (int $code, string $message, ?string $file = null, ?int $line = null) {
            $this->logger->warning($message, ['code' => $code, 'file' => $file, 'line' => $line]);
        }, E_ALL | E_STRICT);

        set_exception_handler(function (Throwable $exception) {
            $this->logger->error($exception->getMessage(), ['code' => $exception->getCode()]);
        });
    }
}
