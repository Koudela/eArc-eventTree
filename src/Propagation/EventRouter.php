<?php
/**
* e-Arc Framework - the explicit Architecture Framework
*
* @package earc/event-tree
* @link https://github.com/Koudela/earc-eventTree/
* @copyright Copyright (c) 2018 Thomas Koudela
* @license http://opensource.org/licenses/MIT MIT License
*/

namespace eArc\EventTree\Propagation;

use eArc\eventTree\Event;
use eArc\EventTree\Handler;
use eArc\ObserverTree\Observer;
use Psr\Container\ContainerInterface;

/**
 * Handles the traveling of the event and the observer calls. (Users of this
 * library must not care about this class. Direct interaction with this class
 * is advised against strongly.)
 */
class EventRouter
{
    const PHASE_START = 1;
    const PHASE_BEFORE = 2;
    const PHASE_DESTINATION = 4;
    const PHASE_BEYOND = 8;
    const PHASE_ACCESS = 15;

    /* may change in each layer: */

    /** @var int */
    protected $eventPhase;

    /* changes in each layer: */

    /** @var int */
    protected $depth;

    /** @var int */
    protected $nodesInLayerCnt;

    /** @var array */
    protected $currentChildren;

    /* changes between observer visits: */

    /** @var int */
    protected $state;

    /** @var int */
    protected $nthChild;

    /* can not change */

    /** @var int|null */
    protected $maxDepth;

    /** @var array */
    protected $path;

    /** @var Event */
    protected $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->maxDepth = $event->getType()->getMaxDepth();
        $this->path = $this->event->getType()->getDestination();
    }

    /**
     * Start the propagation of the event.
     */
    public function dispatchEvent(): void
    {
        $this->event->getHandler()->transferState($this);
        $this->state = 0;

        $this->currentChildren = [$this->event->getType()->getStartNode()];
        $this->nodesInLayerCnt = 1;
        $this->nthChild = 0;
        $this->depth = 0;

        $this->eventPhase = self::PHASE_START;

        $this->visitObserver($this->currentChildren[$this->nthChild]);

        $this->nextNode();
    }

    /**
     * Calculates the travel to the next observer.
     */
    protected function nextNode(): void
    {
        if (0 !== $this->state & Handler::EVENT_IS_TIED) {
            if (0 !== $this->state & Handler::EVENT_IS_TERMINATED) {
                return;
            }
            $this->currentChildren = [$this->currentChildren[$this->nthChild]];
        }

        if (0 !== $this->state & Handler::EVENT_IS_TERMINATED) {
            unset($this->currentChildren[$this->nthChild]);
            if (empty($this->currentChildren)) {
                return;
            }
        }

        ++$this->nthChild;

        $this->state = 0;

        if ($this->nthChild >= $this->nodesInLayerCnt || count($this->currentChildren) === 1) {
            ++$this->depth;

            if ($this->isBeyondMaxDepth()) {
                return;
            }

            $this->currentChildren = $this->getNextNodeLayer();

            if (empty($this->currentChildren)) {
                return;
            }
        }

        $this->visitObserver($this->currentChildren[$this->nthChild]);

        $this->nextNode();
    }

    /**
     * Whether the maximal depth is reached.
     *
     * @return bool
     */
    protected function isBeyondMaxDepth(): bool
    {
        return null !== $this->maxDepth && $this->depth >= $this->maxDepth;
    }

    /**
     * Get all observer nodes that are visited in the next layer.
     *
     * @return array
     */
    protected function getNextNodeLayer(): array
    {
        $cnt = count($this->path);
        $this->nthChild = 0;

        if ($cnt > $this->depth) {
            $this->eventPhase = self::PHASE_BEYOND;

            $children = [];

            foreach ($this->currentChildren as $child)
            {
                /* @var Observer $child */
                foreach ($child->getChildren() as $newChild) {
                    $children[] = $newChild;
                }
            }

            $this->nodesInLayerCnt = count($children);

            return $children;
        }

        $this->nodesInLayerCnt = 1;

        $this->eventPhase = $cnt < $this->depth ? self::PHASE_BEFORE : self::PHASE_DESTINATION;

        return [$this->currentChildren[0]->getChild($this->path[$this->depth -1])];
    }

    /**
     * Get the container if attached as 'container' to the root payload or null
     * otherwise.
     *
     * @return mixed|null
     */
    protected function getContainer(): ?ContainerInterface
    {
        /** @var Event $rootEvent */
        $rootEvent = $this->event->getRoot();

        return $rootEvent->getPayload()->has('container') ? $rootEvent->get('container') : null;
    }

    /**
     * Defines how the observer calls the listener.
     *
     * @param Observer $observer
     */
    protected function visitObserver(Observer $observer): void
    {
        $eventRouter = $this;

        $observer->callListeners(
            $this->event,
            $this->eventPhase,
            function() use ($eventRouter) {
                $state = $eventRouter->event->getHandler()->transferState($eventRouter);
                return 0 !== $state & Handler::EVENT_IS_SILENCED ? Observer::CALL_LISTENER_BREAK : null;
            },
            $this->getContainer()
        );

        $this->event->getHandler()->transferState($this);
    }

    /**
     * Set additional state. Is called by Event::transferState().
     *
     * @param int $state
     */
    public function setState(int $state): void
    {
        $this->state = $this->state | $state;
    }
}
