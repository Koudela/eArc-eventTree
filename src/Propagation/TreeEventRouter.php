<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * event tree component
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-eventTree/
 * @copyright Copyright (c) 2018-2019 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Propagation;

use eArc\EventTree\Interfaces\RoutingTypeInterface;
use eArc\eventTree\TreeEvent;
use eArc\EventTree\Handler;
use eArc\EventTree\Interfaces\TreeEventRouterInterface;
use eArc\Observer\Interfaces\ObserverInterface;
use eArc\ObserverTree\Interfaces\ObserverTreeInterface;

/**
 * Handles the traveling of the event and the observer calls. (Users of this
 * library must not care about this class. Direct interaction with this class
 * is advised against strongly.)
 */
class TreeEventRouter implements TreeEventRouterInterface
{
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

    /** @var TreeEvent */
    protected $event;

    /** @var RoutingTypeInterface */
    protected $routingType;

    /**
     * @param TreeEvent $event
     * @param RoutingTypeInterface $routingType
     */
    public function __construct(TreeEvent $event, RoutingTypeInterface $routingType)
    {
        $this->event = $event;
        $this->routingType = $routingType;
    }

    /**
     * Start the propagation of the event.
     */
    public function dispatchEvent(): void
    {
        $this->state = 0;

        $this->currentChildren = [$this->routingType->getStartNode()];
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
     * Check whether the maximal depth is reached.
     *
     * @return bool
     */
    protected function isBeyondMaxDepth(): bool
    {
        return null !== $this->routingType->getMaxDepth()
            && $this->depth >= $this->routingType->getMaxDepth();
    }

    /**
     * Get all observer nodes that are visited in the next layer.
     *
     * @return array
     */
    protected function getNextNodeLayer(): array
    {
        $path = $this->routingType->getDestination();
        $cnt = count($path);
        $this->nthChild = 0;

        if ($cnt < $this->depth) {
            $this->eventPhase = self::PHASE_BEYOND;

            $children = [];

            foreach ($this->currentChildren as $child)
            {
                /* @var ObserverTreeInterface $child */
                foreach ($child->getChildren() as $newChild) {
                    $children[] = $newChild;
                }
            }

            $this->nodesInLayerCnt = count($children);

            return $children;
        }

        $this->nodesInLayerCnt = 1;

        $this->eventPhase = $cnt < $this->depth ? self::PHASE_BEFORE : self::PHASE_DESTINATION;

        return [$this->currentChildren[0]->getChild($path[$this->depth -1])];
    }

    /**
     * Defines how the observer calls the listener.
     *
     * @param ObserverTreeInterface $observer
     */
    protected function visitObserver(ObserverTreeInterface $observer): void
    {
        $eventRouter = $this;

        $observer->callListeners(
            $this->event,
            $this->eventPhase,
            function() use ($eventRouter) {
                return 0 !== $eventRouter->getState() & Handler::EVENT_IS_SILENCED ?
                    ObserverInterface::CALL_LISTENER_BREAK : null;
            },
            null,
            null
        );
    }

    /**
     * Get state.
     *
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * Set state.
     *
     * @param int $state
     */
    public function setState(int $state): void
    {
        $this->state = $this->state | $state;
    }
}
