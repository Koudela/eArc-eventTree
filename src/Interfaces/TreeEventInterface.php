<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * event tree component
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-eventTree/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Interfaces;

use eArc\EventTree\Exceptions\IsDispatchedException;
use eArc\EventTree\Exceptions\IsNotDispatchedException;
use eArc\EventTree\Interfaces\Propagation\HandlerInterface;
use eArc\EventTree\Interfaces\Propagation\PropagationTypeInterface;
use eArc\EventTree\Interfaces\Transformation\TransitionInfoInterface;
use eArc\Observer\Interfaces\EventInterface;

/**
 * Extended event interface.
 */
interface TreeEventInterface extends EventInterface
{
    /**
     * Dispatches the event on its tree according to its type.
     *
     * @throws IsDispatchedException If it is dispatched already.
     */
    public function dispatch(): void;

    /**
     * Get the handler for the state of the tree event.
     *
     * @return HandlerInterface
     *
     * @throws IsNotDispatchedException If the event is not dispatched yet.
     */
    public function getHandler(): HandlerInterface;

    /**
     * Get the propagation type of the tree event.
     *
     * @return PropagationTypeInterface
     */
    public function getPropagationType(): PropagationTypeInterface;

    /**
     * Get the info object of the current transition state.
     *
     * @return TransitionInfoInterface
     */
    public function getTransitionInfo(): TransitionInfoInterface;

    /**
     * Returns the transition change state the event is in.
     */
    public function getTransitionChangeState(): int;

    /**
     * Set the transition change state of the event.
     *
     * @param int $state
     */
    public function setTransitionChangeState(int $state);
}
