<?php

namespace eArc\eventTree\Interfaces;

use eArc\eventTree\Tree\EventTree;

interface PropagationType
{
    public function getTree(): ?EventTree;

    public function getStart(): array;

    public function getDestination(): array;

    public function getMaxDepth(): ?int;
}
