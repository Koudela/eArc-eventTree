<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/earc-eventTree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Event;

use eArc\EventTree\Exceptions\InvalidDestinationNodeException;
use eArc\EventTree\Exceptions\InvalidStartNodeException;
use eArc\EventTree\Tree\Observer;
use eArc\EventTree\Tree\ObserverRoot;
use Psr\Container\ContainerInterface;

/**
 * Defines an immutable propagatable event type by four properties.
 */
class PropagatableEventType extends PayloadContainer
{
    /** @var ObserverRoot */
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
     * @param PropagatableEventType|null $parent
     * @param ObserverRoot|null $tree
     * @param array $start
     * @param array $destination
     * @param int|null $maxDepth
     * @param bool $inheritPayload
     * @param null|ContainerInterface $container
     *
     * @throws InvalidStartNodeException
     * @throws InvalidDestinationNodeException
     */
    public function __construct(
        ?PropagatableEventType $parent = null,
        ObserverRoot $tree = null,
        array $start = [],
        array $destination = [],
        ?int $maxDepth = 0,
        bool $inheritPayload = false,
        ?ContainerInterface $container = null
    ) {
        $this->tree = $tree;
        $this->start = $start;
        $this->destination = $destination;
        $this->maxDepth = $maxDepth;
        $this->startNode = $this->tree;
        foreach ($start as $name) {
            try {
                $this->startNode = $this->startNode->getChild($name);
            } catch (\Exception $exception) {
                throw new InvalidStartNodeException();
            }
        }
        $this->destinationNode = $this->startNode;
        foreach ($destination as $name) {
            try {
                $this->destinationNode = $this->destinationNode->getChild($name);
            } catch (\Exception $exception) {
                throw new InvalidStartNodeException();
            }
        }
        parent::__construct($parent, $inheritPayload, $container);
    }

    /**
     * Get the observer tree the event uses.
     *
     * @return ObserverRoot|null
     */
    public function getTree(): ?ObserverRoot
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
