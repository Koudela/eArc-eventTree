<?php

namespace eArc\eventTree\Interfaces;


interface PropagationHandler
{
    public function silence(): void;

    public function isSilenced(): bool;

    public function endSilence(): void;

    public function terminate(): void;

    public function isTerminated(): bool;

    public function tie(): void;

    public function isTied(): bool;

    public function reanimate(): void;
}