<?php
declare(strict_types=1);

namespace Warship;

use Exception;
use Psr\Log\LoggerInterface;

class Client {
    const BOARD_WATER = 1;
    const BOARD_BOAT = 2;
    const BOARD_SHOT = 4;

    private array $boards;
    private array $lifes;
    private ?string $myShotCoord;
    private array $adjacentLookingCoords;
    private array $diagonalLookingCoords;

    public function __construct(private ?LoggerInterface $logger = null)
    {
        $this->reset();
    }

    /**
     * convert positions (X,Y) to coords ([A-J][1-10])
     */
    public static function getCoord(int $x, int $y): string {
        return chr(65 + $x) . ($y + 1);
    }

    /**
     * convert coords ([A-J][1-10]) to positions (X,Y)
     */
    public static function getPosition(string $coord): array {
        if(preg_match('`^([A-J])(:?)([1-9]|10)$`', $coord, $m) === 1) {
            return [
                'x' => ord($m[1]) - 65,
                'y' => intval($m[3]) - 1,
            ];
        }

        throw new Exception('Invalid coord provided');
    }

    public function getBoard(string $player = 'my')
    {
        return $this->boards[$player];
    }

    public function displayBoard(string $player = 'my', string $mode = 'raw')
    {
        $display = "";
        for($i = 0; $i < 10; $i++) {
            for($j = 0; $j < 10; $j++) {
                $coord = $this->getCoord($i, $j);
                if($mode === 'raw') {
                    $display .= $this->boards[$player][$coord];
                } else if($mode === 'shot') {
                    if($this->boards[$player][$coord] === (self::BOARD_WATER | self::BOARD_BOAT | self::BOARD_SHOT)) {
                        $display .= 'X';
                    } else if($this->boards[$player][$coord] === (self::BOARD_WATER | self::BOARD_SHOT)) {
                        $display .= 'O';
                    } else {
                        $display .= ' ';
                    }
                }
            }
            $display .= "\n";
        }
        $display .= "\n";

        if($this->logger) {
            $this->logger->info("board $player :\n" . $display);
        }

        return $display;
    }

    /**
     * reset game
     */
    public function reset(): void
    {
        $this->boards = [
            'my' => [],
            'ennemy' => []
        ];
        for($x = 0; $x < 10; $x++) {
            for($y = 0; $y < 10; $y++) {
                $coord = $this->getCoord($x, $y);
                $this->boards['my'][$coord] = self::BOARD_WATER;
                $this->boards['ennemy'][$coord] = self::BOARD_WATER;
            }
        }
        $this->lifes = [
            'my' => 0,
            'ennemy' => 0
        ];
        $this->myShotCoord = null;
        $this->adjacentLookingCoords = [];
        $this->diagonalLookingCoords = [];
        for($x = 0; $x < 10; $x+=2) {
            for($y = 0; $y < 10; $y+=2) {
                $this->diagonalLookingCoords[] = $this->getCoord($x, $y);
            }
        }
        shuffle($this->diagonalLookingCoords);
    }

    /**
     * place boats on my board
     */
    public function setup(): void {
        $boatLengths = array(5, 4, 3, 3, 2);
        foreach($boatLengths as $boatLength) {
            do {
                $x = mt_rand(0, 9);
                $y = mt_rand(0, 9);
                $isHorizontal = mt_rand(0, 1) === 0;
            } while(!$this->canPlaceBoat($x, $y, $boatLength, $isHorizontal));

            $this->placeBoat($x, $y, $boatLength, $isHorizontal);
        }
    }

    public function canPlaceBoat(int $x, int $y, int $length, bool $isHorizontal): bool {
        for($k = -1; $k < $length + 1; $k++) {
            for($l = -1; $l <= 1; $l++) {
                $coord = $isHorizontal ? $this->getCoord($x + $l, $y + $k) : $this->getCoord($x + $k, $y + $l);
                if(!isset($this->boards['my'][$coord]) || $this->boards['my'][$coord] !== self::BOARD_WATER) {
                    return false;
                }
            }
        }

        return true;
    }

    public function placeBoat(int $x, int $y, int $length, bool $isHorizontal) {
        for($i = 0; $i < $length; $i++) {
            $coord = $isHorizontal ? $this->getCoord($x, $y + $i) : $this->getCoord($x + $i, $y);
            $this->boards['my'][$coord] = self::BOARD_BOAT;
            $this->lifes['my']++;
            $this->lifes['ennemy']++;
        }
    }

    /**
     * ia algorithm find next coord as follow
     * - for any hit on ennemy board, then try adjacent coord.
     * - if no hit was found on board, then try all diagonal case coord at random.
     * - at last try case coord at random.
     */
    public function iaShot(): string {
        // adjacent looking coords
        while(count($this->adjacentLookingCoords) > 0) {
            $coord = array_pop($this->adjacentLookingCoords);
            if(isset($this->boards['ennemy'][$coord]) && $this->boards['ennemy'][$coord] === self::BOARD_WATER) {
                return $coord;
            }
        }

        // diagonal looking coords
        while(count($this->diagonalLookingCoords) > 0) {
            $coord = array_pop($this->diagonalLookingCoords);
            if($this->boards['ennemy'][$coord] === self::BOARD_WATER) {
                return $coord;
            }
        }

        // random shot strategy
        do {
            $x = mt_rand(0, 9);
            $y = mt_rand(0, 9);
            $coord = $this->getCoord($x, $y);
        } while($this->boards['ennemy'][$coord] !== self::BOARD_WATER);

        return $coord;
    }

    public function shot(string $coord = null): string {
        if($coord === null) {
            $coord = $this->iaShot();
        }

        $this->boards['ennemy'][$coord] |= self::BOARD_SHOT;
        $this->myShotCoord = $coord;

        return $coord;
    }

    public function shotResponse($flag): string {
        $this->boards['ennemy'][$this->myShotCoord] |= self::BOARD_SHOT;
        $this->boards['ennemy'][$this->myShotCoord] |= $flag;

        if($flag === self::BOARD_BOAT) {
            $this->lifes['ennemy']--;

            $hitPosition = $this->getPosition($this->myShotCoord);
            $this->adjacentLookingCoords[] = $this->getCoord($hitPosition['x'] + 1, $hitPosition['y'] + 0);
            $this->adjacentLookingCoords[] = $this->getCoord($hitPosition['x'] + 0, $hitPosition['y'] + 1);
            $this->adjacentLookingCoords[] = $this->getCoord($hitPosition['x'] - 1, $hitPosition['y'] + 0);
            $this->adjacentLookingCoords[] = $this->getCoord($hitPosition['x'] + 0, $hitPosition['y'] - 1);
            shuffle($this->adjacentLookingCoords);
        }

        return "ok";
    }

    public function ennemyShot($coord): string {
        $this->boards['my'][$coord] |= self::BOARD_SHOT;

        if($this->boards['my'][$coord] & self::BOARD_WATER) {
            return 'miss';
        }

        $this->lifes['my']--;
        if($this->lifes['my'] > 0) {
            return 'hit';
        }

        return 'won';
    }

    public function handleCommand($command): string {
        if ($command === 'your turn') {
            return $this->shot();
        } elseif (preg_match('`^([A-J])(:?)([1-9]|10)$`i', $command, $m) === 1) {
            return $this->ennemyShot($m[1].$m[3]);
        } elseif ($command === 'miss') {
            return $this->shotResponse(self::BOARD_WATER);
        } elseif (preg_match('`^hit|sunk|won$`x', $command)) {
            return $this->shotResponse(self::BOARD_BOAT);
        }

        throw new Exception('command not found');
    }
}