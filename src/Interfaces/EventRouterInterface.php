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

/**
 * Interface for classes handling the traveling of the event and the observer
 * calls.
 */
interface EventRouterInterface
{
    /**
     * Start the propagation of the event.
     */
    public function dispatchEvent(): void;

    /**
     * Set additional state. Is called by Event::transferState().
     *
     * @param int $state
     */
    public function setState(int $state): void;
}