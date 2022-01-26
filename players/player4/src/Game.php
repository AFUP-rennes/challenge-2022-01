<?php

declare(strict_types=1);

namespace Challenge;

final class Game
{
    private Grid $grid;

    private array $boatLifes;

    private AlgorythmInterface $algorythm;

    public function __construct(Grid $grid, array $boatLifes)
    {
        $this->grid = $grid;
        $this->boatLifes = $boatLifes;
        $this->algorythm = new HuntAlgorythm();
    }

    public function play(string $coordinate): void
    {
        $cell = $this->grid->getCell(new Coordinate($coordinate));
        if ($cell > 0) {
            $this->boatLifes[$cell]--;
            if ($this->boatLifes[$cell] > 0) {
                echo "hit\n";
            } else {
                unset($this->boatLifes[$cell]);
                if (count($this->boatLifes) === 0) {
                    echo "won\n";
                } else {
                    echo "sunk\n";
                }
            }
            $this->grid->clearCell(new Coordinate($coordinate));
        } else {
            echo "miss\n";
        }
    }

    public function shoot(): void
    {
        echo $this->algorythm->shoot()."\n";
    }

    public function answer($reply): void
    {
        $this->algorythm->answer($reply);
    }
}
