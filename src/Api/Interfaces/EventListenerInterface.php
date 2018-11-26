<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/earc-eventTree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Api\Interfaces;

use eArc\eventTree\Event\Event;

/**
 * Interface a class must implement to become an EventListener.
 */
interface EventListenerInterface
{
    /**
     * Method which is called by the Observer the EventListener is attached to.
     *
     * @param Event $event
     *
     * @return mixed
     */
    public function processEvent(Event $event): void;
}
