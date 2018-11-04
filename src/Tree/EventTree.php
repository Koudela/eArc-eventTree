<?php

namespace eArc\eventTree\Tree;

class EventTree extends EventLeaf
{
    protected $identifier;

    public function __construct(string $identifier)
    {
        $this->root = $this;
        parent::__construct($this);
        $this->identifier = $identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }
}