<?php

namespace eArc\eventTree\Event;

use Psr\Container\ContainerInterface;

class RootEvent extends Event
{
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->tree = null;
        $this->start = [];
        $this->destination = [];
        $this->maxDepth = 0;
        $this->parent = null;
        $this->silencePropagation();
        $this->terminateSelf();
        $this->terminateOthers();
    }
}
