<?php

namespace eArc\eventTree\Event;

use eArc\eventTree\Transformation\ObserverTreeFactory;
use eArc\eventTree\Tree\EventRouter;

class EventDispatcher
{
    protected $treeFactory;
    protected $parentOfNewEvent;
    protected $tree;
    protected $start;
    protected $destination;
    protected $maxDepth;
    protected $inheritPayload = false;

    public function __construct(ObserverTreeFactory $treeFactory, Event $event)
    {
        $this->treeFactory = $treeFactory;
        $this->parentOfNewEvent = $event;
        $this->tree = $event->getTree();
        $this->start = $event->getStart();
        $this->destination = $event->getDestination();
        $this->maxDepth = $event->getMaxDepth();
    }

    public function tree(string $eventTreeName): EventDispatcher
    {
        $this->tree = $this->treeFactory->get($eventTreeName);
        return $this;
    }

    public function start(array $start): EventDispatcher
    {
        $this->start = $start;
        return $this;
    }

    public function destination(array $destination): EventDispatcher
    {
        $this->destination = $destination;
        return $this;
    }

    public function maxDepth(?int $maxDepth): EventDispatcher
    {
        $this->maxDepth = $maxDepth;
        return $this;
    }

    public function inheritPayload(bool $inheritPayload = true): EventDispatcher
    {
        $this->inheritPayload = $inheritPayload;
        return $this;
    }

    public function dispatch()
    {
        $event = new Event(
            $this->parentOfNewEvent,
            $this->tree,
            $this->start,
            $this->destination,
            $this->maxDepth,
            $this->inheritPayload
        );
        (new EventRouter($event))->dispatchEvent();
    }
}
