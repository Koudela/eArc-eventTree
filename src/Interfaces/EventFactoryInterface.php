<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/earc-eventTree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Interfaces;

use eArc\eventTree\Event;
use eArc\EventTree\Exceptions\InvalidDestinationNodeException;
use eArc\EventTree\Exceptions\InvalidStartNodeException;
use eArc\PayloadContainer\Exceptions\PayloadOverwriteException;
use eArc\ObserverTree\Observer;

/**
 * Interface for factories building events.
 */
interface EventFactoryInterface
{
    /**
     * Set the ObserverTree the event traverses.
     *
     * @param Observer $observerTree
     *
     * @return EventFactoryInterface
     */
    public function tree(Observer $observerTree): EventFactoryInterface;

    /**
     * Set the starting node for the event.
     *
     * @param array $start
     *
     * @return EventFactoryInterface
     */
    public function start(array $start = array()): EventFactoryInterface;

    /**
     * Set the destination node of the event.
     *
     * @param array $destination
     *
     * @return EventFactoryInterface
     */
    public function destination(array $destination = array()): EventFactoryInterface;

    /**
     * Set the maximal depth the event travels from the start node.
     *
     * @param int|null $maxDepth
     *
     * @return EventFactoryInterface
     */
    public function maxDepth(?int $maxDepth = null): EventFactoryInterface;

    /**
     * Set to true if the event shall start with the same payload its parent
     * has by now.
     *
     * @param bool $inheritPayload
     *
     * @return EventFactoryInterface
     */
    public function inheritPayload(bool $inheritPayload = true): EventFactoryInterface;

    /**
     * Add payload to the event.
     *
     * @param string $key
     * @param $payload
     * @param bool $overwrite
     *
     * @return EventFactoryInterface
     */
    public function addPayload(string $key, $payload, $overwrite = false): EventFactoryInterface;

    /**
     * Dispatches the event with a new event router class.
     *
     * @param string $eventRouter
     *
     * @return EventFactoryInterface
     */
    public function setRouter(string $eventRouter): EventFactoryInterface;

    /**
     * Builds the event with a new referenced event factory class.
     *
     * @param string $eventFactory
     *
     * @return EventFactoryInterface
     */
    public function setFactory(string $eventFactory): EventFactoryInterface;

    /**
     * Builds the event. The observer tree, maxDepth, starting and destination
     * node are inherit by the parent if not set. If inheritPayload is not set
     * to true the event starts with a new payload.
     *
     * @return Event
     *
     * @throws InvalidStartNodeException The start node does not exist on the
     * tree.
     * @throws InvalidDestinationNodeException The destination node does not
     * exist on the tree.
     * @throws PayloadOverwriteException The inherited Payload has the an item
     * of the same name as added by addPayload and overwrite was set to false.
     */
    public function build(): Event;
}
