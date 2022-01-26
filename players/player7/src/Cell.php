<?php
declare(strict_types=1);

class Cell
{
    private CELL_STATUS $status;
    public function __construct(
        private int $column,
        private int $line
    ){
        $this->status = CELL_STATUS::Unknown();
    }

    public function getStatus(): CELL_STATUS
    {
        return $this->status;
    }

    public function getKey(): string
    {
        return "$this->column$this->line";
    }

    public function setStatus(CELL_STATUS $status): void
    {
        $this->status = $status;
    }

    public function getNorthKey(): string
    {
        return sprintf("%s%s",$this->column,$this->line-1);
    }

    public function getNorthEastKey(): string
    {
        return sprintf("%s%s",$this->column+1,$this->line-1);
    }

    public function getEastKey(): string
    {
        return sprintf("%s%s",$this->column+1,$this->line);
    }

    public function getSouthEastKey(): string
    {
        return sprintf("%s%s",$this->column+1,$this->line+1);
    }

    public function getSouthKey(): string
    {
        return sprintf("%s%s",$this->column,$this->line+1);
    }

    public function getSouthWestKey(): string
    {
        return sprintf("%s%s",$this->column-1,$this->line+1);
    }

    public function getWestKey(): string
    {
        return sprintf("%s%s",$this->column-1,$this->line);
    }

    public function getNorthWestKey(): string
    {
        return sprintf("%s%s",$this->column-1,$this->line-1);
    }
}