<?php

namespace eArc\eventTree\Interfaces;

use eArc\eventTree\Event\Event;

interface EventInheritanceHandler
{
    public function getParent(): Event;

    public function getChildren(): array;

    public function addChild(Event $event): void;
}