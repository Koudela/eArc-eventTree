<?php

namespace eArc\eventTree\traits;

use eArc\eventTree\Tree\EventLeaf;
use eArc\eventTree\Tree\EventTree;

trait TreeHeritable
{
    protected $parent = null;
    protected $children = [];
    protected $root;

    public function getParent(): EventLeaf
    {
        return $this->parent;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    abstract public function addChild(string $name): EventLeaf;

    public function getChild(string $name): EventLeaf
    {
        return $this->children[$name];
    }

    public function getRoot(): EventTree
    {
        return $this->root;
    }

}