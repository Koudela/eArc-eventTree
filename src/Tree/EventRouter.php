<?php

namespace eArc\eventTree\Tree;

use eArc\eventTree\Event\Event;

class EventRouter
{
    protected $currentLeaf;
    protected $event;
    protected $depth;
    protected $path;
    protected $currentChildren;
    protected $nthChild;

    public function __construct(Event $event)
    {
        $this->currentLeaf = $event->getTree();
        $this->event = $event;
    }

    public function getEventPhase(): string
    {
        if (0 === $this->depth)
        {
            return 'start';
        }

        $cnt = count($this->event->getDestination());

        if ($cnt > $this->depth)
        {
            return 'before';
        }

        if ($cnt === $this->depth)
        {
            return 'destination';
        }

        if ($cnt < $this->depth)
        {
            return 'beyond';
        }
    }
    public function getEvent(): Event
    {
        return $this->event;
    }

    public function dispatchEvent(): void
    {
        if ($this->event->isSelfTerminated()
            || $this->currentLeaf !== $this->currentLeaf->getRoot()
        ) {
            return;
        }

        if ($this->event->isSilenced())
        {
            $this->event->endSilence();
        }

        $this->currentLeaf = $this->currentLeaf->getRoot();

        foreach ($this->event->getStart() as $name)
        {
            $this->currentLeaf->getChild($name);
        }

        $this->depth = 0;

        $this->path = $this->event->getDestination();

        $this->currentChildren = [];

        $this->nthChild = 0;

        $this->currentLeaf->dispatchEvent($this);
    }

    public function next(): ?ObserverLeaf
    {
        if ($this->event->areOthersTerminated())
        {
            $this->currentChildren = [$this->currentLeaf];

            $this->nthChild = 0;
        }

        if ($this->event->isSelfTerminated())
        {
            unset($this->currentChildren[$this->nthChild]);
        }

        if (empty($this->currentChildren))
        {
            return null;
        }

        $this->event->endTermination();

        if (null !== $this->event->getMaxDepth()
            && $this->depth >= $this->event->getMaxDepth()
        ) {
            return null;
        }

        if (++$this->nthChild > count($this->currentChildren))
        {
            $this->currentChildren = $this->getNextChildren();

            $this->depth++;

            if (empty($this->currentChildren))
            {
                return null;
            }

            $this->nthChild = 0;
        }

        return $this->currentChildren[$this->nthChild];
    }

    protected function getNextChildren(): array
    {
        if (isset($this->path[$this->depth]))
        {
            if ($this->event->isSelfTerminated())
            {
                return [];
            }

            return [$this->currentLeaf->getChild($this->path[$this->depth])];
        }

        $children = [];

        foreach ($this->currentChildren as $child)
        {
            /** @var ObserverLeaf $child */
            array_push($children, ...$child->getChildren());
        }

        return $children;
    }
}
