<?php

namespace eArc\eventTree\Tree;

use eArc\eventTree\Interfaces\EventListener;
use Psr\Container\ContainerInterface;

class EventLeaf
{
    protected $listener = [];
    protected $children = [];
    protected $parent;
    protected $root;

    public function __construct(?EventLeaf $parent)
    {
        $this->parent = $parent;
        $this->root = $parent ?? $this;
    }

    public function dispatchEvent(EventRouter $eventRouter): void
    {
        $event = $eventRouter->getEvent();

        sort($this->listener);

        foreach($this->listener as $FQN => $priority)
        {
            if ($event->isSilenced())
            {
                $event->endSilence();
                break;
            }

            $this->getListener($event->getContainer(), $FQN)->processEvent($event);
        }

        if ($nextLeaf = $eventRouter->next())
        {
            $nextLeaf->dispatchEvent($eventRouter);
        }
    }

    protected function getListener(?ContainerInterface $container, string $FQN): EventListener
    {
        if ($container && $container->has($FQN))
        {
            return $container->get($FQN);
        }

        return new $FQN();
    }

    public function registerListener(string $FQN, int $patience): void
    {
        $this->listener[$FQN] = $patience;
    }

    public function unRegisterListener(string $FQN): void
    {
        unset($this->listener[$FQN]);
    }

    public function newChild(string $name): EventLeaf
    {
        $leaf = new EventLeaf($this);

        $this->children[$name] = $leaf;

        return $leaf;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getChild(string $name): EventLeaf
    {
        return $this->children[$name];
    }

    public function getParent(): ?EventLeaf
    {
        return $this->parent;
    }

    public function getRoot(): EventLeaf
    {
        return $this->root;
    }
}
