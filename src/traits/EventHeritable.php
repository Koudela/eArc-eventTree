<?php

namespace eArc\eventTree\traits;

use eArc\eventTree\Event\Event;

trait EventHeritable
{
    protected $parent = null;
    protected $children = [];

    public function getParent(): Event
    {
        return $this->parent;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function addChild(Event $event): void
    {
        $this->children[] = $event;
    }

}