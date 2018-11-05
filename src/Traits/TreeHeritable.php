<?php

namespace eArc\eventTree\Traits;

use eArc\eventTree\Tree\ObserverLeaf;
use eArc\eventTree\Tree\ObserverTree;

trait TreeHeritable
{
    protected $parent = null;
    protected $children = [];
    protected $root;

    public function getParent(): ObserverLeaf
    {
        return $this->parent;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    abstract public function addChild(string $name): ObserverLeaf;

    public function getChild(string $name): ObserverLeaf
    {
        return $this->children[$name];
    }

    public function getRoot(): ObserverTree
    {
        return $this->root;
    }

}