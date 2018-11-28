<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/earc-eventTree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree;

use eArc\EventTree\Exceptions\InvalidDestinationNodeException;
use eArc\EventTree\Exceptions\InvalidStartNodeException;
use eArc\ObserverTree\Observer;

/**
 * Defines an immutable four dimensional event type.
 */
class Type
{
    /** @var Observer */
    protected $tree;

    /** @var array */
    protected $start;

    /** @var array */
    protected $destination;

    /** @var int|null */
    protected $maxDepth;

    /** @var Observer */
    protected $startNode;

    /** @var Observer */
    protected $destinationNode;

    /**
     * @param Observer $tree
     * @param array         $start
     * @param array         $destination
     * @param int|null      $maxDepth
     *
     * @throws InvalidStartNodeException
     * @throws InvalidDestinationNodeException
     */
    public function __construct(
        Observer $tree,
        array $start = [],
        array $destination = [],
        ?int $maxDepth = 0
    ) {
        $this->tree = $tree;
        $this->start = $start;
        $this->destination = $destination;
        $this->maxDepth = $maxDepth;

        if (!$this->startNode = $tree->getPathChild($start)) {
            throw new InvalidStartNodeException();
        }

        if (!$this->destinationNode = $this->startNode->getPathChild($destination)) {
            throw new InvalidDestinationNodeException();
        }
    }

    /**
     * Get the observer tree the event uses.
     *
     * @return Observer|null
     */
    public function getTree(): ?Observer
    {
        return $this->tree;
    }

    /**
     * Get the path to the start observer.
     *
     * @return array
     */
    public function getStart(): array
    {
        return $this->start;
    }

    /**
     * Get the path from the start to the destination observer.
     *
     * @return array
     */
    public function getDestination(): array
    {
        return $this->destination;
    }

    /**
     * Get the maximal depth the event travels from the start node or null if
     * there is no maximal depth.
     *
     * @return int|null
     */
    public function getMaxDepth(): ?int
    {
        return $this->maxDepth;
    }

    /**
     * Get the Observer of the start node.
     *
     * @return Observer
     */
    public function getStartNode(): Observer
    {
        return $this->startNode;
    }

    /**
     * Get the Observer of the destination node.
     *
     * @return Observer
     */
    public function getDestinationNode(): Observer
    {
        return $this->destinationNode;
    }
}
