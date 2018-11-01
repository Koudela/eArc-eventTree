<?php

namespace eArc\eventTree\Event;

use eArc\eventTree\Tree\EventLeaf;
use Psr\Container\ContainerInterface;

class RootEvent extends Event
{
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->parent = null;
        $this->silencePropagation();
        $this->terminateSelf();
        $this->terminateOthers();
    }

    public function getIdentifier(): string
    {
        return 'root';
    }

    public function getTree(): ?EventLeaf
    {
        return null;
    }

    public function getStart(): array
    {
        return [];
    }

    public function getDestination(): array
    {
        return [];
    }

    public function getMaxDepth(): ?int
    {
        return 0;
    }
}
