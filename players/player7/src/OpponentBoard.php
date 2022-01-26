<?php
declare(strict_types=1);

class OpponentBoard extends Board
{
    private string $lastHitShot = '';
    private string $lastShot = '';
    private array $principalTargets = [];

    public function saveResult(Request $request): void
    {
        if ($request->equals(Request::MISS())) {
            unset($this->container[$this->lastShot]);
        } elseif ($request->equals(Request::HIT())) {
            $this->hit();
        } elseif ($request->equals(Request::SUNK())) {
            $this->sunk();
        } else {
            throw new LogicException("This code should not be reached !");
        }
    }

    private function hit(): void
    {
        if (count($this->principalTargets) > 0) {
            $this->filterPrincipalTargets();
        }
        $this->setRegularNeighborsAsPrincipalTargets(...str_split($this->lastShot));
        $this->setDiagonalNeighborsAsEmpty(...str_split($this->lastShot));
        $this->lastHitShot = $this->lastShot;
        unset($this->container[$this->lastShot]);
    }

    private function sunk(): void
    {
        if (count($this->principalTargets) > 0) {
            $this->setPrincipalTargetsAsEmpty();
        }
        $this->setDiagonalNeighborsAsEmpty(...str_split($this->lastShot));
        $this->lastHitShot = $this->lastShot;
        unset($this->container[$this->lastShot]);
    }

    private function setRegularNeighborsAsPrincipalTargets(string $c, string $l): void
    {
        $cell = $this->container["$c$l"];
        $this->principalTargets = array_merge(
            $this->principalTargets, array_filter([
            $this->keyOrNull($cell->getNorthKey()),
            $this->keyOrNull($cell->getEastKey()),
            $this->keyOrNull($cell->getSouthKey()),
            $this->keyOrNull($cell->getWestKey()),
        ]));
    }

    private function filterPrincipalTargets(): void
    {
        foreach ($this->principalTargets as $k => $key) {
            if (!$this->areSameAxis($key, $this->lastShot, $this->lastHitShot)
                || $key === $this->lastHitShot) {
                unset($this->container[$key]);
                unset($this->principalTargets[$k]);
            }
        }
    }

    private function setPrincipalTargetsAsEmpty(): void
    {
        foreach ($this->principalTargets as $key) {
            unset($this->container[$key]);
        }
        $this->principalTargets = [];
    }

    private function setDiagonalNeighborsAsEmpty(string $c, string $l): void
    {
        $cell = $this->container["$c$l"];
        $array = array_filter([
            $this->keyOrNull($cell->getNorthEastKey()),
            $this->keyOrNull($cell->getSouthEastKey()),
            $this->keyOrNull($cell->getSouthWestKey()),
            $this->keyOrNull($cell->getNorthWestKey()),
        ]);
        foreach ($array as $key) {
            unset($this->container[$key]);
        }
    }

    public function takeDecision(): string
    {
        if (!empty($this->principalTargets)) {
            $key = str_pad("" . array_pop($this->principalTargets), 2, "0", STR_PAD_LEFT);
            $this->lastShot = $key;
            return $key;
        }
        $array = array_filter($this->container,
            function ($key) {
                [$c, $l] = str_split("$key");
                return $c % 2 === $l % 2;
            },
            ARRAY_FILTER_USE_KEY);
        $key = str_pad("" . array_rand($array), 2, "0", STR_PAD_LEFT);
        $this->lastShot = $key;
        return $key;
    }

    public function areSameAxis(string $first, string $second, string $third): bool
    {
        return ($first[0] === $second[0] && $second[0] === $third[0])
            || ($first[1] === $second[1] && $second[1] === $third[1]);
    }
}