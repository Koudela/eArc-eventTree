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

namespace eArc\eventTree\Interfaces\Transformation;

use eArc\EventTree\Exceptions\InvalidObserverNodeException;
use eArc\EventTree\Interfaces\TreeEventInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

interface ObserverTreeInterface extends ListenerProviderInterface
{
    /**
     * @param TreeEventInterface $event
     *
     * @return iterable
     *
     * @throws InvalidObserverNodeException
     */
    public function getListenersForEvent($event): iterable;
}
