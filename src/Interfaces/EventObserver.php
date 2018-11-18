<?php

namespace eArc\eventTree\Interfaces;

use eArc\eventTree\Tree\EventRouter;

interface EventObserver
{
    public function callListeners(EventRouter $eventRouter): void;

    public function registerListener(string $FQN, int $eventPhases, float $patience): void;

    public function unregisterListener(string $FQN): void;
}