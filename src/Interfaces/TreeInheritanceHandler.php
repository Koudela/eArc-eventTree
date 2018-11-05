<?php

namespace eArc\eventTree\Interfaces;

use eArc\eventTree\Tree\ObserverLeaf;
use eArc\eventTree\Tree\ObserverTree;

interface TreeInheritanceHandler
{
    public function getParent(): ObserverLeaf;

    public function getChildren(): array;

    public function addChild(string $name): ObserverLeaf;

    public function getChild(string $name): ObserverLeaf;

    public function getRoot(): ObserverTree;
}