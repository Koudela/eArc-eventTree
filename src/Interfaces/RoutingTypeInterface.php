<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 * event tree component
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-eventTree/
 * @copyright Copyright (c) 2018-2019 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Interfaces;

use eArc\ObserverTree\Interfaces\ObserverTreeInterface;

/**
 * Defines an immutable four dimensional event routing type.
 */
interface RoutingTypeInterface
{
    /**
     * Get the observer tree the event uses.
     *
     * @return ObserverTreeInterface
     */
    public function getTree(): ObserverTreeInterface;

    /**
     * Get the path to the start observer.
     *
     * @return string[]
     */
    public function getStart(): array;

    /**
     * Get the path from the start to the destination observer.
     *
     * @return string[]
     */
    public function getDestination(): array;

    /**
     * Get the maximal depth the event travels from the start node or null if
     * there is no maximal depth.
     *
     * @return int|null
     */
    public function getMaxDepth(): ?int;

    /**
     * Get the Observer of the start node.
     *
     * @return ObserverTreeInterface
     */
    public function getStartNode(): ObserverTreeInterface;

    /**
     * Get the Observer of the destination node.
     *
     * @return ObserverTreeInterface
     */
    public function getDestinationNode(): ObserverTreeInterface;
}
