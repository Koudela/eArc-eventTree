<?php

namespace eArc\eventTree\Event;

use Psr\Container\ContainerInterface;

class RootEvent extends Event
{
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(
        EventDispatcherFactory $eventDispatcherFactory,
        ?ContainerInterface $container = null)
    {
        $this->eventDispatcherFactory = $eventDispatcherFactory;
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

    public function toString(): string
    {
        $str = "root:\n";
        $str .= $this->parameterToString($this);
        $str .= "  children:\n";
        $str .= $this->childrenToString($this, '    ');
        return $str;
    }

    protected function parameterToString(Event $event, $indent = '  '): string
    {
        $treeIdentifier = $event->getTree() ? $event->getTree()->getIdentifier() : '';
        return
            $indent . 'tree: ' . $treeIdentifier . "\n" .
            $indent . 'start: [' . implode(', ', $event->getStart()) . "]\n" .
            $indent . 'destination: [' . implode(', ', $event->getDestination()) . "]\n" .
            $indent . 'maxDepth: ' . $event->getMaxDepth() ?? 'null' . "\n";
    }

    protected function childrenToString(Event $parent, $indent = '  '): string
    {
        $str = '';

        foreach ($parent->getChildren() as $name => $event)
        {
            $str .= $indent . $name . ":\n";
            $str .= $this->parameterToString($event, $indent . '  ');
            $str .= $indent . "  children:\n";
            $str .= $this->childrenToString($event, $indent . '    ');
        }

        return $str;
    }
}
