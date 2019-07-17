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

use eArc\Event\Interfaces\EventInterface;
use eArc\EventTree\Exceptions\IsDispatchedException;
use eArc\EventTree\Exceptions\IsRootEventException;

/**
 * Extended event interface.
 */
interface TreeEventInterface extends EventInterface
{
    /**
     * Dispatches the event on its tree according to its type.
     *
     * @throws IsRootEventException If called on a root event.
     * @throws IsDispatchedException If it is dispatched already.
     */
    public function dispatch(): void;

    /**
     * Get a tree event factory which inherits payload and routing type settings
     * from the current event.
     *
     * @return TreeEventFactoryInterface
     */
    public function fork(): TreeEventFactoryInterface;

    /**
     * Get the handler for the state of the tree event.
     *
     * @return HandlerInterface
     */
    public function getHandler(): HandlerInterface;
}