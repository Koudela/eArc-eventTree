<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * event tree component
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-eventTree/
 * @copyright Copyright (c) 2018-2020 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Interfaces\Propagation;

/**
 * Defines a three dimensional boolean state the event is in.
 */
interface HandlerInterface
{
    const EVENT_IS_CLEAN = 0;
    const EVENT_IS_FORWARDED = 1;
    const EVENT_IS_TIED = 2;
    const EVENT_IS_TERMINATED = 4;

    /**
     * If the event is forwarded. It does not activate the listeners of the
     * current observer which it has not activated yet.
     *
     * @return HandlerInterface
     */
    public function forward(): HandlerInterface;

    /**
     * If the event is tied. It does not visit any observers that are not a
     * descendant of the observer node the event is currently on.
     *
     * @return HandlerInterface
     */
    public function tie(): HandlerInterface;

    /**
     * If the event is terminated. It does not visit the descendants of the
     * current observer node.
     *
     * @return HandlerInterface
     */
    public function terminate(): HandlerInterface;

    /**
     * No listener is called hereafter. Is the same a calling forward, tie and terminate
     * in a row.
     *
     * @return HandlerInterface
     */
    public function kill(): HandlerInterface;
}
