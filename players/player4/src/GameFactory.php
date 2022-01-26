<?php

declare(strict_types=1);

namespace Challenge;

final class GameFactory
{
    private Grid $grid;

    private array $boatLife = [];

    private array $generated = [
        'a:5:{i:1;a:3:{s:8:"position";s:2:"G4";s:8:"vertical";b:0;s:4:"size";i:2;}i:2;a:3:{s:8:"position";s:2:"C3";s:8:"vertical";b:0;s:4:"size";i:4;}i:3;a:3:{s:8:"position";s:2:"I6";s:8:"vertical";b:0;s:4:"size";i:5;}i:4;a:3:{s:8:"position";s:2:"A5";s:8:"vertical";b:0;s:4:"size";i:3;}i:5;a:3:{s:8:"position";s:2:"E7";s:8:"vertical";b:0;s:4:"size";i:3;}}',
        'a:5:{i:1;a:3:{s:8:"position";s:2:"A6";s:8:"vertical";b:1;s:4:"size";i:3;}i:2;a:3:{s:8:"position";s:2:"G9";s:8:"vertical";b:0;s:4:"size";i:2;}i:3;a:3:{s:8:"position";s:2:"E6";s:8:"vertical";b:0;s:4:"size";i:5;}i:4;a:3:{s:8:"position";s:2:"I3";s:8:"vertical";b:0;s:4:"size";i:4;}i:5;a:3:{s:8:"position";s:2:"A1";s:8:"vertical";b:0;s:4:"size";i:3;}}',
        'a:5:{i:1;a:3:{s:8:"position";s:2:"C5";s:8:"vertical";b:1;s:4:"size";i:5;}i:2;a:3:{s:8:"position";s:3:"D10";s:8:"vertical";b:1;s:4:"size";i:3;}i:3;a:3:{s:8:"position";s:2:"D3";s:8:"vertical";b:1;s:4:"size";i:4;}i:4;a:3:{s:8:"position";s:2:"J4";s:8:"vertical";b:0;s:4:"size";i:2;}i:5;a:3:{s:8:"position";s:2:"A5";s:8:"vertical";b:0;s:4:"size";i:3;}}',
        'a:5:{i:1;a:3:{s:8:"position";s:3:"A10";s:8:"vertical";b:1;s:4:"size";i:3;}i:2;a:3:{s:8:"position";s:2:"E8";s:8:"vertical";b:1;s:4:"size";i:3;}i:3;a:3:{s:8:"position";s:2:"G4";s:8:"vertical";b:0;s:4:"size";i:2;}i:4;a:3:{s:8:"position";s:2:"I5";s:8:"vertical";b:0;s:4:"size";i:4;}i:5;a:3:{s:8:"position";s:2:"A1";s:8:"vertical";b:0;s:4:"size";i:5;}}',
        'a:5:{i:1;a:3:{s:8:"position";s:2:"G6";s:8:"vertical";b:1;s:4:"size";i:3;}i:2;a:3:{s:8:"position";s:2:"C4";s:8:"vertical";b:1;s:4:"size";i:5;}i:3;a:3:{s:8:"position";s:2:"J2";s:8:"vertical";b:0;s:4:"size";i:3;}i:4;a:3:{s:8:"position";s:2:"A2";s:8:"vertical";b:0;s:4:"size";i:4;}i:5;a:3:{s:8:"position";s:2:"F9";s:8:"vertical";b:0;s:4:"size";i:2;}}',
        'a:5:{i:1;a:3:{s:8:"position";s:2:"G2";s:8:"vertical";b:0;s:4:"size";i:5;}i:2;a:3:{s:8:"position";s:2:"A3";s:8:"vertical";b:0;s:4:"size";i:2;}i:3;a:3:{s:8:"position";s:2:"B7";s:8:"vertical";b:0;s:4:"size";i:3;}i:4;a:3:{s:8:"position";s:2:"I7";s:8:"vertical";b:0;s:4:"size";i:4;}i:5;a:3:{s:8:"position";s:2:"F8";s:8:"vertical";b:0;s:4:"size";i:3;}}',
        'a:5:{i:1;a:3:{s:8:"position";s:2:"B2";s:8:"vertical";b:0;s:4:"size";i:3;}i:2;a:3:{s:8:"position";s:2:"I2";s:8:"vertical";b:0;s:4:"size";i:3;}i:3;a:3:{s:8:"position";s:2:"H9";s:8:"vertical";b:0;s:4:"size";i:2;}i:4;a:3:{s:8:"position";s:2:"F4";s:8:"vertical";b:0;s:4:"size";i:5;}i:5;a:3:{s:8:"position";s:2:"B6";s:8:"vertical";b:0;s:4:"size";i:4;}}'
    ];

    public function __construct()
    {
        $this->grid = new Grid();
    }

    public function createGame(): Game
    {
        return new Game($this->grid, $this->boatLife);
    }

    public function addBoatVertically(int $size, int $boatId, ?Coordinate $coordinate = null): Coordinate
    {
        if ($coordinate === null) {
            $coordinate = new Coordinate(chr(mt_rand(65, 74)).mt_rand(1, 10));
        }

        while (! $this->grid->canAddBoatVertically($coordinate, $size)) {
            $coordinate = new Coordinate(chr(mt_rand(65, 74)).mt_rand(1, 10));
        }

        $this->grid->addBoatVertically($coordinate, $size, $boatId);
        $this->boatLife[$boatId] = $size;
        return $coordinate;
    }

    public function addBoatHorizontally(int $size, int $boatId, ?Coordinate $coordinate = null): Coordinate
    {
        if ($coordinate === null) {
            $coordinate = new Coordinate(chr(mt_rand(65, 74)).mt_rand(1, 10));
        }

        while (! $this->grid->canAddBoatHorizontally($coordinate, $size)) {
            $coordinate = new Coordinate(chr(mt_rand(65, 74)).mt_rand(1, 10));
        }

        $this->grid->addBoatHorizontally($coordinate, $size, $boatId);
        $this->boatLife[$boatId] = $size;
        return $coordinate;
    }

    public function loadFromGenerated(): Game
    {
        $mapId = mt_rand(0, count($this->generated)-1);
        $boats = unserialize($this->generated[$mapId]);
        foreach ($boats as $id => $boat) {
            if ($boat['vertical']) {
                $this->addBoatVertically($boat['size'], $id, new Coordinate($boat['position']));
            } else {
                $this->addBoatHorizontally($boat['size'], $id, new Coordinate($boat['position']));
            }
        }

        return new Game($this->grid, $this->boatLife);
    }
}
