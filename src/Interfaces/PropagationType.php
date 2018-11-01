<?php

namespace eArc\eventTree\Interfaces;

interface PropagationType
{
    public function getStart(): array;

    public function getDestination(): array;

    public function getMaxDepth(): ?int;
}
