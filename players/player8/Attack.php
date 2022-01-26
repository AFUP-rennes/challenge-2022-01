<?php

class Attack
{
    private Layout $layout;
    private BigBerthaBattle $bigBertha;
    private string $mode;

    /** @var array<int, int> */
    private array $currentBoatIndexes = [];
    private Direction $currentDirection;

    private array $boatsLength = [2,3,3,4,5];

    public function __construct(Layout $layout, BigBerthaBattle $bigBertha)
    {
        $this->mode = 'scan';
        $this->layout = $layout;
        $this->bigBertha = $bigBertha;
        $this->currentDirection = Direction::DOWN();
    }

    public function doAttack(): void
    {
        switch ($this->mode) {
            case 'scan':
                $this->scanMode();
                break;
            case 'huntAround':
                $this->shootAround();
                break;
            case 'huntDirection':
                $this->shootDirection();
                break;
                // ya un hack dans shootDirection()
            case 'huntReverse':
                $this->shootDirection();
                break;
        }
    }

    public function scanMode(): void
    {
        $proba = $this->getProba();
        $index = \array_key_first($proba);
        $this->shoot($index);
    }

    public function handleShootResult(GridIndexKnowledge $gridIndexKnowledge): void
    {
        // Maj la grille
        $this->layout->setGridIndex($this->bigBertha->getLastShoot(), $gridIndexKnowledge);

        // maj le bateau courant
        if ($gridIndexKnowledge->hasHit()) {
            $this->currentBoatIndexes[] = $this->bigBertha->getLastShoot();
        }

        if ($this->mode === 'scan' && $gridIndexKnowledge->equal(GridIndexKnowledge::hit())) {
            $this->mode = 'huntAround';
        } elseif ($this->mode === 'huntAround' && $gridIndexKnowledge->equal(GridIndexKnowledge::hit())) {
            $this->mode = 'huntDirection';
        }

        if ($this->mode === 'huntDirection' && $gridIndexKnowledge->equal(GridIndexKnowledge::miss())) {
            $this->mode = 'huntReverse';
            // on remet bertha sur le 1er impact et on reverse la direction
            $this->bigBertha->setCurrentTarget($this->currentBoatIndexes[0]);
            $this->currentDirection = $this->currentDirection->reverse();
        }

        if ($gridIndexKnowledge->equal(GridIndexKnowledge::sunk())) {
            $this->postBoatSunk();
            $this->mode = 'scan';
        }
    }

    private function shootAround(): void
    {
        do {
            $direction = $this->currentDirection->next();
            $newIndex = $this->layout->move($this->currentBoatIndexes[0], $direction);
            $this->currentDirection = $direction;
        } while ($newIndex === null || $this->isIndexTargetable($newIndex, [$this->currentBoatIndexes[0]]) === false);

        $this->shoot($newIndex);
    }

    private function shootDirection(): void
    {
        $newIndex = $this->layout->move($this->bigBertha->getCurrentTarget(), $this->currentDirection);
        if ($newIndex === null || $this->isIndexTargetable($newIndex, [$this->bigBertha->getCurrentTarget()]) === false) {
            if ($this->mode === 'huntDirection') {
                $this->mode = 'huntReverse';
                $this->bigBertha->setCurrentTarget($this->currentBoatIndexes[0]);
                $this->currentDirection = $this->currentDirection->reverse();
                $this->shootDirection();
            }
        } else {
            $this->shoot($newIndex);
        }
    }

    private function shoot(int $index): void
    {
        $gridIndexKnowledge = $this->layout->getGridIndex($index);
        if ($gridIndexKnowledge->equal(GridIndexKnowledge::unknown())) {
            $this->bigBertha->fire($index);
        }
    }

    public function postBoatSunk(): void
    {
        foreach ($this->currentBoatIndexes as $index) {
            foreach ($this->layout->getValidIndexesCloseTo($index) as $closeIndex) {
                if ($this->layout->getGridIndex($closeIndex)->equal(GridIndexKnowledge::unknown())) {
                    $this->layout->setGridIndex($closeIndex, GridIndexKnowledge::untarget());
                }
            }
        }

        $destroyBoatSize = \count($this->currentBoatIndexes);

        foreach ($this->boatsLength as $k => $attackBoatLength) {
            if ($destroyBoatSize === $attackBoatLength) {
                unset($this->boatsLength[$k]);
                break;
            }
        }

        $this->currentBoatIndexes = [];
    }

    private function isIndexTargetable(int $index, array $removeIndexes = []): bool
    {
        if (!$this->layout->getGridIndex($index)->equal(GridIndexKnowledge::unknown())) {
            return false;
        }
        foreach ($this->layout->getValidIndexesCloseTo($index) as $closeIndex) {
            if (!\in_array($closeIndex, $removeIndexes, true) && $this->layout->getGridIndex($closeIndex)->hasHit()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Proba FTW
     */
    private function getProba(): array
    {
        $tabProba = array_fill(0, Layout::SIZE ** 2, 0);

        foreach ($this->boatsLength as $boatLength) {
            foreach ($this->layout as $index => $gridIndexKnowledge) {

                // 1 - Check si case possible (Règle : unkown && pas de hit ajacent)
                if ($this->isIndexTargetable($index) === false) {
                    continue;
                }

                foreach ([Direction::DOWN(), Direction::RIGHT()] as $direction) {
                    $boatOriIndex = $index;
                    $boatIndexes = [$boatOriIndex];

                    for ($i = 1; $i < $boatLength; $i++) {
                        $newIndex = $this->layout->move($boatOriIndex, $direction);
                        // OPTI possible : Si une case est OK, SI la suivante est un Go RIGHT check juste -MAX+1, +1, +MAX+1 SINON c'est un Go DOWN ALORS check +MAX-1, +MAX, +MAX+1
                        if ($newIndex === null || $this->isIndexTargetable($newIndex) === false) {
                            continue 2;
                        }
                        $boatIndexes[] = $newIndex;
                        $boatOriIndex = $newIndex;
                    }
                    // Le bateau est posable ici
                    foreach ($boatIndexes as $boatIndex) {
                        $tabProba[$boatIndex]++;
                    }
                }
            }
        }
        \arsort($tabProba);

        return $tabProba;
    }
}
