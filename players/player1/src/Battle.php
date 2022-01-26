<?php

declare(strict_types=1);

namespace App;

use App\Exception\ReadCommandException;
use App\Exception\RuntimeException;
use App\Exception\ShipOutOfBoundException;
use App\Exception\ShipOverlapsAnotherException;
use App\Exception\SunkException;

final class Battle
{
    private Fleet $fleet;
    private Grid $grid;
    private ?Coordinate $lastHitCoordinate;
    private int $smallestShip;
    /** @var int[] */
    private array $sunkenShips;

    /**
     * @param int[] $shipSizes
     * @throws \Exception
     */
    public function __construct(private array $shipSizes = [5,4,3,3,2])
    {
        if ($this->shipSizes === []) {
            throw new RuntimeException('$shipSizes must contains at least one ship size');
        }

        foreach ($this->shipSizes as $size) {
            if (!\is_int($size)) {
                throw new RuntimeException('$shipSizes must contains only integer');
            }
        }

        $this->grid = new Grid();
        $this->lastHitCoordinate = null;
        $this->fleet = new Fleet($this->grid->getGridSize());
        foreach ($this->shipSizes as $size) {
            do {
                try {
                    $ship = new Ship(new Coordinate(\random_int(0, 9), \random_int(0, 9)), $size, \random_int(0, 1));
                    $this->fleet->addShip($ship);
                    $retry = false;
                } catch (ShipOverlapsAnotherException | ShipOutOfBoundException) {
                    $retry = true;
                }
            } while ($retry);
        }
        $this->smallestShip = \min($this->shipSizes);
    }

    /**
     * @throws ReadCommandException
     * @throws RuntimeException
     * @throws SunkException
     */
    public function run(): void
    {
        $playing = true;
        while ($playing) {
            $command = $this->readCommand();

            if ($command === 'your turn') {
                $response = $this->getHitCoordinate();
            } elseif (preg_match('`^([A-J](?:[1-9]|10))$`i', $command)) {
                $response = $this->getHitResult($command);
                if ($response === 'won') {
                    $playing = false;
                }
            } elseif (preg_match('`^hit|miss|sunk$`i', $command)) {
                $response = $this->setOpponentHitResult($command);
            } elseif ($command === 'won') {
                $this->sendResponse('ok');
                break;
            } else {
                throw new ReadCommandException("Can't understand '$command'\n");
            }

            $this->sendResponse($response);
        }
    }

    /**
     * @throws SunkException
     */
    private function checkSunkenShipsAndGetTheSmallestShipToSink(): int
    {
        $smallest = \max($this->shipSizes);
        $shipToSink = \array_count_values($this->shipSizes);

        $sunken = \array_count_values($this->sunkenShips);

        foreach ($shipToSink as $size => $count) {
            $sunkenCount = $sunken[$size] ?? 0;
            if ($sunkenCount > $count) {
                throw new SunkException("The number of boats with a size of {$size} is exceeded: {$count}");
            }
            if ($sunkenCount < $count) {
                $smallest = \min($size, $smallest);
            }
        }

        foreach ($sunken as $size => $count) {
            if (!isset($shipToSink[$size])) {
                throw new SunkException('Unknown ship size: ' . $size);
            }
        }

        return (int) $smallest;
    }

    /**
     * @throws RuntimeException
     * @throws SunkException
     */
    private function getHitCoordinate(): string
    {
        $this->lastHitCoordinate = $this->grid->findBestCoordinateToHit($this->smallestShip);

        return $this->lastHitCoordinate->toString();
    }

    private function getHitResult(string $coordinateAsString): string
    {
        $coordinate = Coordinate::fromString($coordinateAsString);
        $ship = $this->fleet->getShipAt($coordinate);
        if ($ship === null) {
            return 'miss';
        }
        $ship->damage($coordinate);

        if ($ship->isSunk()) {
            if ($this->fleet->hasFleetBeenSunk()) {
                return 'won';
            }
            return 'sunk';
        }

        return 'hit';
    }

    /**
     * @throws ReadCommandException
     */
    private function readCommand(): string
    {
        $command = fgets(STDIN);
        if ($command === false) {
            throw new ReadCommandException('error could not read STDIN');
        }

        return \trim($command);
    }

    private function sendResponse(string $string): void
    {
        echo $string, "\n";
    }

    /**
     * @throws ReadCommandException
     * @throws SunkException
     */
    private function setOpponentHitResult(string $command): string
    {
        if ($this->lastHitCoordinate === null) {
            throw new ReadCommandException('error Could not set result of hit without coordinates');
        }

        if ($command === 'miss') {
            $this->grid->miss($this->lastHitCoordinate);
        } elseif ($command === 'hit') {
            $this->grid->hit($this->lastHitCoordinate);
        } else {
            $this->sunkenShips[] = $this->grid->sunk($this->lastHitCoordinate);
            $this->smallestShip = $this->checkSunkenShipsAndGetTheSmallestShipToSink();
        }

        return 'ok';
    }
}
