<?php

namespace eArc\eventTree\Event;

use eArc\eventTree\Interfaces\PropagationType;
use eArc\eventTree\Traits\EventHeritable;
use eArc\eventTree\Traits\PropagatableHandler;
use eArc\eventTree\Tree\ObserverTree;
use eArc\eventTree\Interfaces\EventInheritanceHandler;
use eArc\eventTree\Interfaces\PropagationHandler;
use eArc\eventTree\Traits\PropagatableType;

class Event extends PayloadContainer implements PropagationType, PropagationHandler, EventInheritanceHandler
{
    use PropagatableType;
    use PropagatableHandler;
    use EventHeritable;

    protected $eventDispatcherFactory;

    public function __construct(
        Event $parent,
        ObserverTree $tree,
        array $start = [],
        array $destination = [],
        ?int $maxDepth = null,
        bool $inheritPayload = false
    ) {
        parent::__construct($parent->container);
        $this->tree = $tree;
        $this->start = $start;
        $this->destination = $destination;
        $this->maxDepth = $maxDepth;
        $this->parent = $parent;
        $this->parent->addChild($this);
        if ($inheritPayload) {
            $this->payload = $parent->getPayload();
        }
        $this->eventDispatcherFactory = $parent->getEventDispatcherFactory();
    }

    public function getEventDispatcherFactory(): EventDispatcherFactory
    {
        return $this->eventDispatcherFactory;
    }

    public function new()
    {
        return $this->eventDispatcherFactory->build();
    }

    public function clone()
    {
        return $this->eventDispatcherFactory->build($this);
    }

    public function __clone()
    {
        throw new \BadMethodCallException();
    }
}
