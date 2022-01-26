<?php
declare(strict_types=1);

namespace Battle;

final class Tactic
{
    private const BOARD_MIN = 1;
    private const BOARD_MAX = 10;
    private const OUT_OF_BOARD = false;

    // Phases du jeu
    public const PLAY_YOUR_TURN = 'your turn';
    public const PLAY_WON = 'won';
    public const PLAY_MISS = 'miss';
    public const PLAY_OK = 'ok';
    public const PLAY_ERROR = 'error';

    // Mes constantes de direction
    private const DIRECTION_RIGHT = 'R';
    private const DIRECTION_LEFT = 'L';
    private const DIRECTION_TOP = 'T';
    private const DIRECTION_BOTTOM = 'B';

    private const DIRECTIONS = [
        self::DIRECTION_RIGHT,
        self::DIRECTION_LEFT,
        self::DIRECTION_TOP,
        self::DIRECTION_BOTTOM
    ];

    private const DIRECTIONS_OPPOSITES = [
        self::DIRECTION_RIGHT => self::DIRECTION_LEFT,
        self::DIRECTION_LEFT => self::DIRECTION_RIGHT,
        self::DIRECTION_TOP => self::DIRECTION_BOTTOM,
        self::DIRECTION_BOTTOM => self::DIRECTION_TOP
    ];

    private const DIRECTIONS_FOR_BOATS = [
        self::DIRECTION_RIGHT,
        self::DIRECTION_BOTTOM,
    ];

    /**
     * Les cases que j'ai joué
     * @var array<int, string> $plays
     */
    private array $plays = [];

    // La dernière case jouée
    private string $lastPlay = '';

    // Pour jouer jusqu'à couler le bateau
    private bool $hitFirst = false;
    private string $hitFirstPlay = '';

    private bool $hitSecond = false;
    private string $hitSecondPlay = '';

    private int $directionToTurn = 0;
    private int $directionToOpposite = 0;

    /**
     * Les cases du bateau qu'on est en train de couler
     * @var array<int, string> $currentBoatCases
     */
    private array $currentBoatCases = [];

    // Une direction choisie
    private string $direction;

     /**
     * Les Bateaux
     * @var array<int, Boat> $boats
     */
    private array $boats = [];

    /**
     *  Les coordonnées de tous les bateaux (pour savoir où ils sont tous placés)
     * @var array<int, string> $boatsCoordinates
     */
    private array $boatsCoordinates = [];
    
    /**
     * @var array<int, string> $boatsCoordinatesAndAdjacents
     */
    private array $boatsCoordinatesAndAdjacents = [];

    public function read(string $command): void
    {
        // Je coule, je reset tout
        if ($command === Boat::STATE_SUNK) {
            $this->hitFirst = false;
            $this->hitSecond = false;

            // On prend les cases adjacentes du bateau, un autre bateau ne peut pas s'y trouver
            $adjacentsBoat = $this->allAdjacents($this->currentBoatCases);
            $this->plays = array_unique(array_merge($this->plays, $adjacentsBoat));

            $this->currentBoatCases = [];
        }

        // Je touche
        if ($command === Boat::STATE_HIT) {

            $this->currentBoatCases[] = $this->lastPlay;
            
            // Si c'est la première fois que je touche
            if (!$this->hitFirst) {
                $this->hitFirst = true;
                $this->hitFirstPlay = $this->lastPlay;

                // Réinitialisation
                $this->directionToTurn = 0;
                $this->directionToOpposite = 0;

                return;
            }

            // c'est pas la première fois
            if (!$this->hitSecond) {
                $this->hitSecond = true;
                $this->hitSecondPlay = $this->lastPlay;

                // Réinitialisation
                $this->directionToOpposite = 0;
            }
        }

        // Je rate
        if ($command === self::PLAY_MISS) {

            // J'avais déjà touché
            if ($this->hitFirst) {
                $this->directionToTurn++;

                // On repart au play d'avant
                $this->lastPlay = $this->hitFirstPlay;
            }

            // J'avais déjà touché 2 fois
            if ($this->hitSecond) {
                $this->directionToOpposite++;

                // On repart au play d'avant
                $this->lastPlay = $this->hitSecondPlay;
            }
        }
    }

    public function play(): string
    {
        if ($this->hitFirst) {

            // J'ai touché un premier coup
            $this->direction = self::DIRECTIONS[$this->directionToTurn];
            $case = $this->nextCase($this->lastPlay, $this->direction);

            while ($case === self::OUT_OF_BOARD) {
                $this->directionToTurn++;
                $this->direction = self::DIRECTIONS[$this->directionToTurn];
                $case = $this->nextCase($this->lastPlay, $this->direction);
            }

            $this->plays[] = $case;
            $this->lastPlay = $case;

            return $case;
        }

        if ($this->hitSecond) {

            // J'ai touché un second coup
            if ($this->directionToOpposite === 0) {
                $this->direction = self::DIRECTIONS[$this->directionToTurn];
            } else {
                $this->direction = self::DIRECTIONS_OPPOSITES[self::DIRECTIONS[$this->directionToTurn]];
            }

            $case = $this->nextCase($this->lastPlay, $this->direction);

            if ($case === self::OUT_OF_BOARD) {
                // On est sorti du cadre, je repars dans l'autre sens
                $this->direction = self::DIRECTIONS_OPPOSITES[self::DIRECTIONS[$this->directionToTurn]];
                $this->directionToOpposite++; // Je tourne du coup

                while (true) {
                    $case = $this->nextCase($case, $this->direction);

                    if (!in_array($case, $this->plays)) {
                        break;
                    }
                }
            }

            $this->plays[] = $case;
            $this->lastPlay = $case;

            return $case;
        }

        // Nouveau Play
        $case = $this->getRandomPlay();

        $this->plays[] = $case;
        $this->lastPlay = $case;

        return $case;
    }

    private function getRandomPlay(): string
    {
        // Forcément une case pas encore jouée
        while (true) {
            $case = $this->getRandomCase();

            if (!in_array($case, $this->plays)) {
                break;
            }
        }

        return $case;
    }

    public function checkBoats(string $case): string
    {
        foreach ($this->boats as $boat) {
            $result = $boat->check($case);

            if ($result === Boat::STATE_OK) {
                continue;
            }

            if ($result === Boat::STATE_SUNK) {
                // Bateau coulé, est ce que c'est le dernier ? Sinon juste coulé
                return $this->checkBoatsAllSinked() ? self::PLAY_WON : Boat::STATE_SUNK;
            }

            // Touché
            return Boat::STATE_HIT;
        }

        return self::PLAY_MISS;
    }

    private function checkBoatsAllSinked(): bool
    {
        foreach ($this->boats as $boat) {
            if (!$boat->sinked()) {
                return false;
            }
        }

        // Tous coulés
        return true;
    }

    /**
     * @param array<int> $configBoats
     */
    public function init(array $configBoats): void
    {
        // On initialise les bateaux
        foreach ($configBoats as $configBoat) {
            $this->boats[] = new Boat($configBoat);
        }

        // On va positionner les bateaux
        foreach ($this->boats as $boat) {

            while (true) {

                $coordinates = [];
                $coordinatesAndAdjacents = [];
                $start = $this->getRandomCase($boat->length);

                if (in_array($start, $this->boatsCoordinatesAndAdjacents)) {
                    continue;
                }

                $coordinates[] = $start;
    
                $direction = self::DIRECTIONS_FOR_BOATS[rand(0, 1)];

                for ($l = 1; $l < $boat->length; $l++) {
                    $next = $this->nextCase($start, $direction);

                    if ($next === self::OUT_OF_BOARD) {
                        continue 2; // On est sorti, on recommence
                    }

                    if (in_array($start, $this->boatsCoordinatesAndAdjacents)) {
                        continue 2; // Déjà pris, on recommence
                    }

                    $start = $next;
                    $coordinates[] = $start;
                    $coordinatesAndAdjacents = array_merge($coordinatesAndAdjacents, [$start], $this->adjacents($start));
                    $coordinatesAndAdjacents = array_unique($coordinatesAndAdjacents);
                }

                // On a réussi à tout placer
                $this->boatsCoordinatesAndAdjacents = array_merge(
                    $this->boatsCoordinatesAndAdjacents,
                    $coordinatesAndAdjacents
                );

                $boat->setCoordinates($coordinates);
                $this->boatsCoordinates = array_merge($this->boatsCoordinates, $coordinates);

                $boat->ready = true;

                break;
            }
        }
    }

    private function getRandomCase(int $length = 0): string
    {
        $cols = str_split('_ABCDEFGHIJ');
        return $cols[rand(1, 10 - $length)] . rand(1, 10 - $length);
    }

    public function nextCase(bool|string $case, string $direction):bool|string
    {
        if ($case === self::OUT_OF_BOARD) {
            return self::OUT_OF_BOARD;
        }

        $cols = str_split('_ABCDEFGHIJ');
        [$col, $line] = sscanf($case, '%c%d');

        if ($direction === self::DIRECTION_RIGHT) {
            $line++;
            return ($line > self::BOARD_MAX) ? self::OUT_OF_BOARD : $col . $line;
        }

        if ($direction === self::DIRECTION_LEFT) {
            $line--;
            return ($line < self::BOARD_MIN) ? self::OUT_OF_BOARD : $col . $line;
        }

        if ($direction === self::DIRECTION_BOTTOM) {
            $pos = array_search($col, $cols) + 1;
            return ($pos > self::BOARD_MAX) ? self::OUT_OF_BOARD : $cols[$pos] . $line;
        }

        if ($direction === self::DIRECTION_TOP) {
            $pos = array_search($col, $cols) - 1;
            return ($pos < self::BOARD_MIN) ? self::OUT_OF_BOARD : $cols[$pos] . $line;
        }

        return self::OUT_OF_BOARD; // Sécurité
    }

    /**
     * @return array<int, string>
     */
    private function adjacents(string $case): array
    {
        // 8 adjacents, 3 au dessus, 2 côte côte, 3 en dessous
        $adjacents = [];

        // Dessus
        Utils::array_push_special($adjacents, $this->nextCase($case, self::DIRECTION_TOP));
        Utils::array_push_special($adjacents, $this->nextCase($this->nextCase($case, self::DIRECTION_LEFT), self::DIRECTION_TOP));
        Utils::array_push_special($adjacents, $this->nextCase($this->nextCase($case, self::DIRECTION_RIGHT), self::DIRECTION_TOP));

        // Cote Cote
        Utils::array_push_special($adjacents, $this->nextCase($case, self::DIRECTION_LEFT));
        Utils::array_push_special($adjacents, $this->nextCase($case, self::DIRECTION_RIGHT));

        // Dessous
        Utils::array_push_special($adjacents, $this->nextCase($case, self::DIRECTION_BOTTOM));
        Utils::array_push_special($adjacents, $this->nextCase($this->nextCase($case, self::DIRECTION_LEFT), self::DIRECTION_BOTTOM));
        Utils::array_push_special($adjacents, $this->nextCase($this->nextCase($case, self::DIRECTION_RIGHT), self::DIRECTION_BOTTOM));

        return $adjacents;
    }

    /**
     * @param array<int, string> $cases
     * @return array<int, string>
     */
    private function allAdjacents(array $cases): array
    {
        $adjacents = [];

        foreach ($cases as $case) {
            $adjacents = array_merge($adjacents, $this->adjacents($case));
        }

        return array_unique($adjacents);
    }
}
