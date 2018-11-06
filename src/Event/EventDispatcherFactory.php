<?php

namespace eArc\eventTree\Event;

use eArc\eventTree\Transformation\ObserverTreeFactory;
use Psr\Container\ContainerInterface;

class EventDispatcherFactory
{
    protected $rootEvent;
    protected $observerTreeFactory;

    public function __construct(
        ObserverTreeFactory $observerTreeFactory,
        ?ContainerInterface $container = null
    ) {
        $this->observerTreeFactory = $observerTreeFactory;
        $this->rootEvent = new RootEvent($container);
    }

    public function build(?Event $event = null): EventDispatcher
    {
        return new EventDispatcher($this->observerTreeFactory, $event ?? $this->rootEvent);
    }
}
