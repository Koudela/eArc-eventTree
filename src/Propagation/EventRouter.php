<?php
/**
* e-Arc Framework - the explicit Architecture Framework
*
* @package earc/event-tree
* @link https://github.com/Koudela/earc-eventTree/
* @copyright Copyright (c) 2018 Thomas Koudela
* @license http://opensource.org/licenses/MIT MIT License
*/

namespace eArc\EventTree\Tree;

use eArc\eventTree\Event\Event;
use eArc\EventTree\Event\PropagatableEventHandler;

class EventRouter
{
    const PHASE_START = 1;
    const PHASE_BEFORE = 2;
    const PHASE_DESTINATION = 4;
    const PHASE_BEYOND = 8;
    const PHASE_ACCESS = 15;

    protected $eventPhase;
    protected $currentLeaf;
    protected $event;
    protected $depth;
    protected $path;
    protected $currentChildren;
    protected $nthChild;

    public function __construct(PropagatableEventHandler $event)
    {
        $this->currentLeaf = $event->getTree();
        $this->event = $event;
    }

    public function matchesEventPhase(int $eventPhaseBitMask): bool
    {
        return (0 !== ($eventPhaseBitMask & $this->eventPhase));
    }

    protected function setEventPhase(): void
    {
        $this->nthChild = 0;
        $cnt = count($this->event->getDestination());

        if (0 === $this->depth)
        {
            $this->eventPhase = self::PHASE_START;
        }
        elseif ($cnt > $this->depth)
        {
            $this->eventPhase = self::PHASE_BEFORE;
        }
        elseif ($cnt === $this->depth)
        {
            $this->eventPhase = self::PHASE_DESTINATION;
        }
        elseif ($cnt < $this->depth)
        {
            $this->eventPhase = self::PHASE_BEYOND;
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

        $this->setEventPhase();

        $this->currentLeaf->callListeners($this->event, $this->eventPhase);

        $this->nextLeaf();
    }

    public function nextLeaf(): void
    {
        if ($this->event->isTied())
        {
            $this->currentChildren = [$this->currentLeaf];

            $this->setEventPhase();
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

            $this->setEventPhase();
        }

        $this->currentChildren[$this->nthChild]->callListeners($this->event, $this->eventPhase);

        $this->nextLeaf();
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
            /* @var ObserverLeaf $child */
            foreach ($child->getChildren() as $newChild)
            {
                array_push($children, $newChild);
            }
        }

        return $children;
    }

    /**
     * Transforms the eventPhases to a string representation.
     *
     * @param int $eventPhases
     *
     * @return string
     */
    public static function eventPhasesToString(int $eventPhases): string
    {
        if (EventRouter::PHASE_ACCESS === $eventPhases) {
            return 'access';
        }

        $arr = [];

        if (EventRouter::PHASE_START & $eventPhases) {
            $arr[] = 'start';
        }

        if (EventRouter::PHASE_BEFORE & $eventPhases) {
            $arr[] = 'before';
        }

        if (EventRouter::PHASE_DESTINATION & $eventPhases) {
            $arr[] = 'destination';
        }

        if (EventRouter::PHASE_BEYOND & $eventPhases) {
            $arr[] = 'beyond';
        }

        return implode(' | ', $arr);
    }
}
