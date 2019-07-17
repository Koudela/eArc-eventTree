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
 * Interface for classes handling the traveling of the event and the observer
 * calls.
 */
interface TreeEventRouterInterface
{
    const PHASE_START = 1;
    const PHASE_BEFORE = 2;
    const PHASE_DESTINATION = 4;
    const PHASE_BEYOND = 8;
    const PHASE_ACCESS = 15;

    /**
     * Start the propagation of the event.
     */
    public function dispatchEvent(): void;

    /**
     * Get state.
     *
     * @return int
     */
    public function getState(): int;

    /**
     * Set state.
     *
     * @param int $state
     */
    public function setState(int $state): void;
}