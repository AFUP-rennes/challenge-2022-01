<?php
declare(strict_types=1);

class PersonalBoard extends Board
{
    /** @var array<Boat> $fleet */
    private array $fleet = [];
    public function __construct()
    {
        parent::__construct();
        $this->placeShips();
    }

    private function placeShips(): void
    {
        $ships = [
            new Boat(5),
            new Boat(4),
            new Boat(3),
            new Boat(3),
            new Boat(2),
        ];
        while(count($ships) > 0){
            $randCell = strval(array_rand($this->container));
            $assigned = false;
            for($i=0; (!$assigned)&&($i<count($ships)); $i++){
                $ship = $ships[array_keys($ships)[$i]];
                if($this->tryPlaceBoatFromCell($ship, $randCell)){
                    $this->fleet[] = $ship;
                    unset($ships[array_keys($ships)[$i]]);
                    $assigned = true;
                }
            }
        }
    }

    public function tryPlaceBoatFromCell(Boat $boat, string $coordinates): bool
    {
        if(!$this->tryPlaceBoatOnCells($boat, self::getNorth($coordinates, $boat->getSize()))){
            if(!$this->tryPlaceBoatOnCells($boat, self::getEast($coordinates, $boat->getSize()))){
                if(!$this->tryPlaceBoatOnCells($boat, self::getSouth($coordinates, $boat->getSize()))){
                    if(!$this->tryPlaceBoatOnCells($boat, self::getWest($coordinates, $boat->getSize()))){
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public function tryPlaceBoatOnCells(Boat $boat, array $coordinates): bool
    {
        foreach ($coordinates as $coor){
            if(!$this->isInTheBoard($coor)){
                return false;
            }
            if($this->isFull($coor)){
                return false;
            }
        }
        $boat->setCells($coordinates);
        foreach ($this->getNeighbors($coordinates) as $neighbor){
            $this->container[$neighbor]->setStatus(CELL_STATUS::Full());
        }
        return true;
    }

    /**
     * @param array<string> $cells
     * @return array
     */
    public function getNeighbors(array $cells): array
    {
        $cells = array_map(
            fn(string $key)=>$this->container[$key],
            $cells
        );
        $neighbors = [];
        foreach ($cells as $cell){
            $neighbors = array_merge($neighbors, array_filter([
                    $this->keyOrNull($cell->getNorthKey()),
                    $this->keyOrNull($cell->getNorthEastKey()),
                    $this->keyOrNull($cell->getEastKey()),
                    $this->keyOrNull($cell->getSouthEastKey()),
                    $this->keyOrNull($cell->getSouthKey()),
                    $this->keyOrNull($cell->getSouthWestKey()),
                    $this->keyOrNull($cell->getWestKey()),
                    $this->keyOrNull($cell->getNorthWestKey())
                ]));
        }
        return $neighbors;
    }
    public static function getNorth(string $coordinates, int $size): array
    {
        [$c,$l] = str_split($coordinates);
        $array = [$coordinates];
        foreach (range(1,$size-1) as $distance){
            $array[] = sprintf("%s%s",$c,$l-$distance);
        }
        return $array;
    }

    public static function getEast(string $coordinates, int $size): array
    {
        [$c,$l] = str_split($coordinates);
        $array = [$coordinates];
        foreach (range(1,$size-1) as $distance){
            $array[] = sprintf("%s%s",$c+$distance,$l);
        }
        return $array;
    }

    public static function getSouth(string $coordinates, int $size): array
    {
        [$c,$l] = str_split($coordinates);
        $array = [$coordinates];
        foreach (range(1,$size-1) as $distance){
            $array[] = sprintf("%s%s",$c,$l+$distance);
        }
        return $array;
    }

    public static function getWest(string $coordinates, int $size): array
    {
        [$c,$l] = str_split($coordinates);
        $array = [$coordinates];
        foreach (range(1,$size-1) as $distance){
            $array[] = sprintf("%s%s",$c-$distance,$l);
        }
        return $array;
    }

    public function hitCell(string $coordinates): Response
    {
        foreach ($this->fleet as $ship){
            if($ship->isOnCell($coordinates)){
                $ship->hitCell($coordinates);
                $response = $ship->isSunk()
                    ? Response::SUNK()
                    : Response::HIT();
            }
        }
        if(isset($response) && $response->equals(Response::SUNK())){
            foreach ($this->fleet as $ship){
                if(!$ship->isSunk()){
                    return $response;
                }
            }
            return Response::WON();
        }
        return $response ?? Response::MISS();
    }
}