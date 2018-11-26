<?php

namespace eArc\EventTree\Transformation;

use eArc\EventTree\Api\Interfaces\EventFactoryInterface;
use eArc\EventTree\Event\Event;
use eArc\EventTree\Tree\ObserverRoot;

class EventFactory implements EventFactoryInterface
{
    protected $parentOfNewEvent;
    protected $tree;
    protected $start;
    protected $destination;
    protected $maxDepth;
    protected $inheritPayload = false;
    protected $payload = [];

    public function __construct(Event $event)
    {
        $this->parentOfNewEvent = $event;
        $this->tree = $event->getTree();
        $this->start = $event->getStart();
        $this->destination = $event->getDestination();
        $this->maxDepth = $event->getMaxDepth();
    }

    public function tree(ObserverRoot $observerTree): EventFactoryInterface
    {
        $this->tree = $observerTree;
        return $this;
    }

    public function start(array $start = array()): EventFactoryInterface
    {
        $this->start = $start;
        return $this;
    }

    public function destination(array $destination = array()): EventFactoryInterface
    {
        $this->destination = $destination;
        return $this;
    }

    public function maxDepth(?int $maxDepth = null): EventFactoryInterface
    {
        $this->maxDepth = $maxDepth;
        return $this;
    }

    public function inheritPayload(bool $inheritPayload = true): EventFactoryInterface
    {
        $this->inheritPayload = $inheritPayload;
        return $this;
    }

    public function addPayload(string $key, $payload): EventFactoryInterface
    {
        $this->payload[$key] = $payload;
        return $this;
    }

    public function build(): Event
    {
        if (!$this->tree instanceof ObserverRoot)
        {
            throw new \InvalidArgumentException(
                'On using a root event as parent, selecting an observer tree is mandatory.'
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

        return $event;
    }
}
