<?php

namespace eArc\eventTree\Interfaces;

use eArc\eventTree\Tree\ObserverTree;

interface PropagationType
{
    public function getTree(): ?ObserverTree;

    public function getStart(): array;

    public function getDestination(): array;

    public function getMaxDepth(): ?int;
}
