<?php

namespace eArc\eventTree\Interfaces;

use eArc\eventTree\Tree\EventRouter;

interface EventObserver
{
    public function dispatchEvent(EventRouter $eventRouter): void;

    public function registerListener(string $FQN, string $type, int $patience): void;

    public function unRegisterListener(string $FQN): void;
}