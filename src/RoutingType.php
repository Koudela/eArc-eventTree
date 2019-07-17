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

namespace eArc\EventTree;

use eArc\EventTree\Exceptions\InvalidDestinationNodeException;
use eArc\EventTree\Exceptions\InvalidStartNodeException;
use eArc\EventTree\Interfaces\RoutingTypeInterface;
use eArc\ObserverTree\Interfaces\ObserverTreeInterface;
use eArc\Tree\Exceptions\NotFoundException;

/**
 * Defines an immutable four dimensional event routing type.
 */
class RoutingType implements RoutingTypeInterface
{
    /** @var ObserverTreeInterface */
    protected $observerTree;

    /** @var string[] */
    protected $start;

    /** @var string[] */
    protected $destination;

    /** @var int|null */
    protected $maxDepth;

    /** @var ObserverTreeInterface */
    protected $startNode;

    /** @var ObserverTreeInterface */
    protected $destinationNode;

    /**
     * @param ObserverTreeInterface $observerTree
     * @param string[]              $start
     * @param string[]              $destination
     * @param int|null              $maxDepth
     *
     * @throws InvalidStartNodeException
     * @throws InvalidDestinationNodeException
     */
    public function __construct(
        ObserverTreeInterface $observerTree,
        array $start = [],
        array $destination = [],
        ?int $maxDepth = 0
    ) {
        $this->observerTree = $observerTree->getRoot();
        $this->start = $start;
        $this->destination = $destination;
        $this->maxDepth = $maxDepth;

        try {
            $this->startNode = $observerTree->getPathChild($start);
        } catch (NotFoundException $exception) {
            throw new InvalidStartNodeException();
        }

        try {
            $this->destinationNode = $observerTree->getPathChild($destination);
        } catch (NotFoundException $exception) {
            throw new InvalidDestinationNodeException();
        }
    }

    /**
     * @inheritdoc
     */
    public function getTree(): ObserverTreeInterface
    {
        return $this->observerTree;
    }

    /**
     * @inheritdoc
     */
    public function getStart(): array
    {
        return $this->start;
    }

    /**
     * @inheritdoc
     */
    public function getDestination(): array
    {
        return $this->destination;
    }

    /**
     * @inheritdoc
     */
    public function getMaxDepth(): ?int
    {
        return $this->maxDepth;
    }

    /**
     * @inheritdoc
     */
    public function getStartNode(): ObserverTreeInterface
    {
        return $this->startNode;
    }

    /**
     * @inheritdoc
     */
    public function getDestinationNode(): ObserverTreeInterface
    {
        return $this->destinationNode;
    }
}
