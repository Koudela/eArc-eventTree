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

namespace eArc\EventTree\Interfaces\Propagation;

use eArc\eventTree\Interfaces\EventDispatcherInterface;

/**
 * Defines an immutable four dimensional event routing type.
 */
interface PropagationTypeInterface
{
    /**
     * Get the event dispatcher responsible for the event.
     *
     * @return EventDispatcherInterface
     */
    public function getDispatcher(): EventDispatcherInterface;

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
     * Get the maximal depth the event travels from the destination node or null
     * if it is not limited.
     *
     * @return int|null
     */
    public function getMaxDepth(): ?int;
}
