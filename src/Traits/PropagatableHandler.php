<?php

namespace eArc\eventTree\Traits;

trait PropagatableHandler
{
    protected $isSilenced = false;
    protected $isTerminated = false;
    protected $isTied = false;

    public function silence(): void
    {
        $this->isSilenced = true;
    }

    public function isSilenced(): bool
    {
        return $this->isSilenced;
    }

    public function endSilence(): void
    {
        $this->isSilenced = false;
    }

    public function terminate(): void
    {
        $this->isTerminated = true;
    }

    public function isTerminated(): bool
    {
        return $this->isTerminated;
    }

    public function tie(): void
    {
        $this->isTied = true;
    }

    public function isTied(): bool
    {
        return $this->isTied;
    }

    public function reanimate(): void
    {
        $this->isTerminated = false;
        $this->isTied = false;
    }

    public function kill(): void
    {
        $this->terminate();
        $this->tie();
    }
}