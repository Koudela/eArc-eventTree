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

namespace eArc\EventTree\Interfaces;

use eArc\Container\Exceptions\ItemOverwriteException;
use eArc\EventTree\Exceptions\InvalidDestinationNodeException;
use eArc\EventTree\Exceptions\InvalidStartNodeException;
use eArc\ObserverTree\Interfaces\ObserverTreeInterface;

/**
 * Interface for factories building events.
 */
interface TreeEventFactoryInterface
{
    /**
     * Set the ObserverTree the event traverses.
     *
     * @param ObserverTreeInterface $observerTree
     *
     * @return TreeEventFactoryInterface
     */
    public function tree(ObserverTreeInterface $observerTree): TreeEventFactoryInterface;

    /**
     * Set the starting node for the event.
     *
     * @param string[] $start
     *
     * @return TreeEventFactoryInterface
     */
    public function start(array $start = array()): TreeEventFactoryInterface;

    /**
     * Set the destination node of the event.
     *
     * @param string[] $destination
     *
     * @return TreeEventFactoryInterface
     */
    public function destination(array $destination = array()): TreeEventFactoryInterface;

    /**
     * Set the maximal depth the event travels from the start node.
     *
     * @param int|null $maxDepth
     *
     * @return TreeEventFactoryInterface
     */
    public function maxDepth(?int $maxDepth = null): TreeEventFactoryInterface;

    /**
     * Set to true if the event shall start with the same payload its parent
     * has by now.
     *
     * @param bool $inheritPayload
     *
     * @return TreeEventFactoryInterface
     */
    public function inheritPayload(bool $inheritPayload = true): TreeEventFactoryInterface;

    /**
     * Add payload to the event.
     *
     * @param string $key
     * @param $payload
     * @param bool $overwrite
     *
     * @return TreeEventFactoryInterface
     */
    public function addPayload(string $key, $payload, $overwrite = false): TreeEventFactoryInterface;

    /**
     * Dispatches the event with a new event router class.
     *
     * @param string $eventRouterClass
     *
     * @return TreeEventFactoryInterface
     */
    public function setRouter(string $eventRouterClass): TreeEventFactoryInterface;

    /**
     * Builds the event with a new referenced event factory class.
     *
     * @param string $eventFactoryClass
     *
     * @return TreeEventFactoryInterface
     */
    public function setFactory(string $eventFactoryClass): TreeEventFactoryInterface;

    /**
     * Builds the event. The observer tree, maxDepth, starting and destination
     * node are inherit by the parent if not set. If inheritPayload is not set
     * to true the event starts with a new payload.
     *
     * @return TreeEventInterface
     *
     * @throws InvalidStartNodeException The start node does not exist on the
     * tree.
     * @throws InvalidDestinationNodeException The destination node does not
     * exist on the tree.
     * @throws ItemOverwriteException The inherited Payload has the an item
     * of the same name as added by addPayload and overwrite was set to false.
     */
    public function build(): TreeEventInterface;
}
