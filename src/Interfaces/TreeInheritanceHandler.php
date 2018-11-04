<?php

namespace Interfaces;

use eArc\eventTree\Tree\EventLeaf;
use eArc\eventTree\Tree\EventTree;

interface TreeInheritanceHandler
{
    public function getParent(): EventLeaf;

    public function getChildren(): array;

    public function addChild(string $name): EventLeaf;

    public function getChild(string $name): EventLeaf;

    public function getRoot(): EventTree;
}