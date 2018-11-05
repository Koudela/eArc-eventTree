<?php

namespace eArc\eventTree\Event;

use eArc\eventTree\Exceptions\ContainerHasChangedException;
use eArc\eventTree\Tree\EventRouter;
use eArc\eventTree\Tree\EventTree;
use Psr\Container\ContainerInterface;

class EventFactory
{
    protected static $rootEvent;

    protected $parentOfNewEvent;
    protected $tree;
    protected $start;
    protected $destination;
    protected $maxDepth;
    protected $inheritPayload = false;

    public static function getRootEvent(?ContainerInterface $container = null)
    {
        if (!self::$rootEvent)
        {
            self::$rootEvent = new RootEvent($container);
        }

        if (self::$rootEvent->getContainer() !== $container)
        {
            throw new ContainerHasChangedException();
        }

        return self::$rootEvent;
    }

    public function __construct(Event $event)
    {
        $this->parentOfNewEvent = $event;
        $this->tree = $event->getTree();
        $this->start = $event->getStart();
        $this->destination = $event->getDestination();
        $this->maxDepth = $event->getMaxDepth();
    }

    public function tree(EventTree $eventTree): EventFactory
    {
        $this->tree = $eventTree;
        return $this;
    }

    public function start(array $start): EventFactory
    {
        $this->start = $start;
        return $this;
    }

    public function destination(array $destination): EventFactory
    {
        $this->destination = $destination;
        return $this;
    }

    public function maxDepth(?int $maxDepth): EventFactory
    {
        $this->maxDepth = $maxDepth;
        return $this;
    }

    public function inheritPayload(bool $inheritPayload = true): EventFactory
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
