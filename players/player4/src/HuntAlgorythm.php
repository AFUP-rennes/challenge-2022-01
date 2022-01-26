<?php

declare(strict_types=1);

namespace Challenge;

final class HuntAlgorythm implements AlgorythmInterface
{
    private string $lastAnswer = "start";

    private array $tracks = ["top", "right", "bottom", "left"];

    private int $whereTrack = -1;

    private ?Coordinate $startTrack = null;

    private ?string $orientation = null;

    private array $lastHits = [];

    private RandAlgorythm $source;

    public function __construct(array $coordinates = [])
    {
        $this->source = new RandAlgorythm();
        foreach ($coordinates as $coordinate) {
            $this->source->add($coordinate);
        }
    }

    private function getUniqRandomCoordinate(): string
    {
        return $this->source->uniqShoot();
    }

    private function getLastCoordinate(): string
    {
        $coordinates = $this->source->getCoordinates();
        return end($coordinates);
    }

    private function resetTrack(): string
    {
        // Invalide marge as coordinates
        $this->lastHits[] = $this->getLastCoordinate();
        sort($this->lastHits);
        $start = new Coordinate($this->lastHits[0]);
        $start->moveTopSafe();
        $start->moveLeftSafe();
        $end = new Coordinate(end($this->lastHits));
        $end->moveBottomSafe();
        $end->moveRightSafe();

        $startLetter = $start->getY();
        $endLetter = $end->getY();
        $startInteger = $start->getX();
        $endInteger = $end->getX();
        for ($i = $startLetter; $i <= min(74, $endLetter); $i++) {
            for ($j = $startInteger; $j <= min(10, $endInteger); $j++) {
                $coordinate = chr($i).$j."";
                if (!in_array($coordinate, $this->source->getCoordinates())) {
                    $this->source->add($coordinate);
                }
            }
        }

        $this->whereTrack = -1;
        $this->startTrack = null;
        $this->orientation = null;
        $this->lastHits = [];

        return $this->getUniqRandomCoordinate();
    }

    private function track(): string
    {
        if ($this->lastAnswer === "hit") {
            $this->lastHits[] = $this->getLastCoordinate();
        }

        if ($this->startTrack === null && $this->lastAnswer === "miss") {
            return $this->getUniqRandomCoordinate();
        }

        if ($this->startTrack === null) {
            $this->startTrack = new Coordinate($this->getLastCoordinate());
        }

        if ($this->whereTrack !== -1 && $this->lastAnswer === "hit") {
            $last = new Coordinate($this->getLastCoordinate());
            // find some other boat part
            // Where shoot ? Top, Bottom, left or right ?
            // Let's guest orientation: horizontal or vertical
            $this->orientation = $this->startTrack->getY() != $last->getY() ? "vertical" : "horizontal";
            // Then, get direction
            $coordinate = clone $last;
            if ($this->orientation === "vertical") {
                if ($this->startTrack->getY() < $last->getY()) {
                    $coordinate->moveBottom();
                } else {
                    $coordinate->moveTop();
                }
            } else {
                if ($this->startTrack->getX() < $last->getX()) {
                    $coordinate->moveRight();
                } else {
                    $coordinate->moveLeft();
                }
            }
            // If invalide coordinate, try other direction
            if ($coordinate->isValid()) {
                return $coordinate->__toString();
            } else {
                $this->lastAnswer = "miss";
            }
        }

        if ($this->orientation != null && $this->lastAnswer === "miss") {
            $last = new Coordinate($this->getLastCoordinate());
            // Then, get direction
            $coordinate = clone $this->startTrack;
            if ($this->orientation === "vertical") {
                if ($this->startTrack->getY() > $last->getY()) {
                    $coordinate->moveBottom();
                } else {
                    $coordinate->moveTop();
                }
            } else {
                if ($this->startTrack->getX() > $last->getX()) {
                    $coordinate->moveRight();
                } else {
                    $coordinate->moveLeft();
                }
            }
            return $coordinate->__toString();
        }

        do {
            $coordinate = clone $this->startTrack;
            $this->whereTrack++;
            match ($this->tracks[$this->whereTrack]) {
                default => $coordinate->moveTop(),
                "right" => $coordinate->moveRight(),
                "bottom" => $coordinate->moveBottom(),
                "left" => $coordinate->moveLeft()
            };
        } while (!$coordinate->isValid());

        return $coordinate->__toString();
    }

    public function shoot(): string
    {
        if (count($this->source->getCoordinates()) >= 100) {
            return "error [too many shoot, the game must be finish]";
        }

        do {
            $coordinate = match ($this->lastAnswer) {
                default => $this->getUniqRandomCoordinate(),
                'hit' => $this->track(),
                'miss' => $this->track(),
                'sunk' => $this->resetTrack(),
            };
            $this->lastAnswer = "miss";
        } while (in_array($coordinate, $this->source->getCoordinates()));
        $this->source->add($coordinate);
        return $coordinate;
    }

    public function answer(string $reply): void
    {
        $this->lastAnswer = $reply;
    }
}
