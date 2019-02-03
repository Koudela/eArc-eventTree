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

/**
 * Defines a three dimensional boolean state the event is in.
 */
interface HandlerInterface
{
    const EVENT_IS_SILENCED = 1;
    const EVENT_IS_TIED = 2;
    const EVENT_IS_TERMINATED = 4;

    /**
     * If the event is silenced it does not activate the listeners of the
     * current observer which it has not activated yet.
     */
    public function silence(): void;

    /**
     * If the event is tied it does not visit any observers that are not a
     * descendant of the observer node the event is currently on.
     */
    public function tie(): void;

    /**
     * If the event is terminated it does not visit the descendants of the
     * current observer node.
     */
    public function terminate(): void;

    /**
     * A killed event can not leave the current observer node. But it can
     * be handed from the observer down to any remaining listeners.
     */
    public function kill(): void;
}
