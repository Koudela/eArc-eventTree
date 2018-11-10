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
        if ($this->event->isTerminated()
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

        $this->currentChildren = [$this->currentLeaf];

        $this->nthChild = 0;

        $this->currentLeaf->callListeners($this);
    }

    public function nextLeaf(): void
    {
        if ($this->event->isTied())
        {
            $this->currentChildren = [$this->currentLeaf];

            $this->nthChild = 0;
        }

        if ($this->event->isTerminated())
        {
            unset($this->currentChildren[$this->nthChild]);
        }

        if (empty($this->currentChildren))
        {
            return;
        }

        $this->event->reanimate();

        if (null !== $this->event->getMaxDepth()
            && $this->depth >= $this->event->getMaxDepth()
        ) {
            return;
        }

        if (++$this->nthChild >= count($this->currentChildren) || 0 === $this->depth)
        {
            $this->currentChildren = $this->getNextChildren();

            $this->depth++;

            if (empty($this->currentChildren))
            {
                return;
            }

            $this->nthChild = 0;
        }

        $this->currentChildren[$this->nthChild]->callListeners($this);
    }

    protected function getNextChildren(): array
    {
        if (isset($this->path[$this->depth]))
        {
            if ($this->event->isTerminated())
            {
                return [];
            }

            return [$this->currentLeaf->getChild($this->path[$this->depth])];
        }

        $children = [];

        foreach ($this->currentChildren as $child)
        {
            /** @var ObserverLeaf $child */
            foreach ($child->getChildren() as $newChild)
            {
                array_push($children, $newChild);
            }
        }

        return $children;
    }
}
