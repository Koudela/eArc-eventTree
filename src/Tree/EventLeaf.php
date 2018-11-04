<?php

namespace eArc\eventTree\Tree;

use eArc\eventTree\Interfaces\EventObserver;
use eArc\eventTree\traits\Listenable;
use eArc\eventTree\traits\TreeHeritable;
use Interfaces\TreeInheritanceHandler;

class EventLeaf implements EventObserver, TreeInheritanceHandler
{
    use TreeHeritable;
    use Listenable;

    public function __construct(EventLeaf $parent)
    {
        $this->parent = $parent;
        $this->root = $parent->getRoot();
    }

    public function addChild(string $name): EventLeaf
    {
        $leaf = new EventLeaf($this);

        $this->children[$name] = $leaf;

        return $leaf;
    }
}
