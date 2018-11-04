<?php

namespace eArc\eventTree\Event;

use eArc\eventTree\Interfaces\PropagationType;
use eArc\eventTree\traits\EventHeritable;
use eArc\eventTree\traits\PropagatableHandler;
use eArc\eventTree\Tree\EventTree;
use Interfaces\EventInheritanceHandler;
use Interfaces\PropagationHandler;
use traits\PropagatableType;

class Event extends PayloadContainer implements PropagationType, PropagationHandler, EventInheritanceHandler
{
    use PropagatableType;
    use PropagatableHandler;
    use EventHeritable;

    public function __construct(
        Event $parent,
        EventTree $tree,
        array $start = [],
        array $destination = [],
        ?int $maxDepth = null
    ) {
        parent::__construct($parent->container);
        $this->tree = $tree;
        $this->start = $start;
        $this->destination = $destination;
        $this->maxDepth = $maxDepth;
        $this->parent = $parent;
        $this->parent->addChild($this);
    }

    public function new()
    {
        return new EventFactory(EventFactory::getRootEvent());
    }

    public function clone()
    {
        return new EventFactory($this);
    }

    public function __clone()
    {
        throw new \BadMethodCallException();
    }
}
