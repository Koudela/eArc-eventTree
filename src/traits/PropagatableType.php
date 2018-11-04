<?php

namespace traits;

use eArc\eventTree\Tree\EventTree;

trait PropagatableType
{
    protected $tree = null;
    protected $start = array();
    protected $destination = array();
    protected $maxDepth = 0;

    public function getTree(): ?EventTree
    {
        return $this->tree;
    }

    public function getStart(): array
    {
        return $this->start;
    }

    public function getDestination(): array
    {
        return $this->destination;
    }

    public function getMaxDepth(): ?int
    {
        return $this->maxDepth;
    }
}