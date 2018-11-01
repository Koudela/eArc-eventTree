<?php

namespace eArc\eventTree\Interfaces;

use eArc\eventTree\Event\Event;

interface EventHandler
{
    public function silencePropagation(): void;

    public function terminateSelf(): void;

    public function terminateOthers(): void;

    public function endSilence(): void;

    public function isSilenced(): bool;

    public function isSelfTerminated(): bool;

    public function endTermination(): void;

    public function areOthersTerminated(): bool;

    public function dispatchNewEvent(string $FQN): Event;

    public function getParent(): ?Event;

    public function getChildren(): ?array;
}
