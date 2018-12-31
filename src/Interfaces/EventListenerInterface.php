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

use eArc\EventTree\Event;

/**
 * Interface a class must implement to become an EventListener.
 *
 * Hint: This Interface is only for convenience. If you write code more basic
 * than a event listener, for example an event router class, please use the
 * \eArc\ObserverTree\Interfaces\EventListenerInterface for re-usability
 * reasons.
 */
interface EventListenerInterface
{
    /**
     * Method which is called by the Observer the EventListener is attached to.
     *
     * @param Event $event
     *
     * @return mixed|void
     */
    public function process(Event $event);
}
