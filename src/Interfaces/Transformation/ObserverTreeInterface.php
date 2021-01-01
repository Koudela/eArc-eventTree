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

namespace eArc\EventTree\Interfaces\Transformation;

use eArc\EventTree\Exceptions\InvalidObserverNodeException;
use eArc\EventTree\Exceptions\UnsuitableEventException;
use eArc\EventTree\Interfaces\TreeEventInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

interface ObserverTreeInterface extends ListenerProviderInterface
{
    const PHASE_START = 1;
    const PHASE_BEFORE = 2;
    const PHASE_DESTINATION = 4;
    const PHASE_BEYOND = 8;
    const PHASE_ACCESS = 15;

    /**
     * @param TreeEventInterface $event
     *
     * @return iterable
     *
     * @throws InvalidObserverNodeException
     * @throws UnsuitableEventException
     */
    public function getListenersForEvent($event): iterable;
}
