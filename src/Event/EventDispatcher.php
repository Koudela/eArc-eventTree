<?php

namespace eArc\eventTree\Event;

use eArc\eventTree\Transformation\ObserverTreeFactory;
use eArc\eventTree\Tree\EventRouter;
use eArc\eventTree\Tree\ObserverTree;

class EventDispatcher
{
    protected $treeFactory;
    protected $parentOfNewEvent;
    protected $tree;
    protected $start;
    protected $destination;
    protected $maxDepth;
    protected $inheritPayload = false;
    protected $payload = [];

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

    public function start(array $start = array()): EventDispatcher
    {
        $this->start = $start;
        return $this;
    }

    public function destination(array $destination = array()): EventDispatcher
    {
        $this->destination = $destination;
        return $this;
    }

    public function maxDepth(?int $maxDepth = null): EventDispatcher
    {
        $this->maxDepth = $maxDepth;
        return $this;
    }

    public function inheritPayload(bool $inheritPayload = true): EventDispatcher
    {
        $this->inheritPayload = $inheritPayload;
        return $this;
    }

    public function addPayload(string $key, $payload)
    {
        $this->payload[$key] = $payload;
    }

    public function dispatch(?ObserverTree $observerTree = null)
    {
        if (!$observerTree && $this->tree instanceof ObserverTree)
        {
            throw new \InvalidArgumentException(
                'On using the root event as parent, selecting an observer tree is mandatory.'
            );
        }

        $event = new Event(
            $this->parentOfNewEvent,
            $this->tree,
            $this->start,
            $this->destination,
            $this->maxDepth,
            $this->inheritPayload
        );

        foreach ($this->payload as $key => $payload)
        {
            $event->setPayload($key, $payload);
        }

        (new EventRouter($event))->dispatchEvent();
    }
}
