<?php

namespace eArc\eventTree\Interfaces;

use eArc\eventTree\Tree\EventLeaf;

interface EventType
{
    public function getIdentifier(): string;

    public function getTree(): ?EventLeaf;
}
