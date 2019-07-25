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

namespace eArc\eventTree;

use eArc\eventTree\Interfaces\EventDispatcherInterface;
use eArc\eventTree\Interfaces\Transformation\ObserverTreeInterface;
use eArc\EventTree\Interfaces\TreeEventInterface;
use eArc\eventTree\Transformation\ObserverTree;

class EventDispatcher implements EventDispatcherInterface
{
    /** @var ObserverTreeInterface */
    protected $observerTree;

    public function __construct()
    {
        $this->observerTree = di_is_decorated(ObserverTreeInterface::class)
            ? di_get(ObserverTreeInterface::class)
            : di_get(ObserverTree::class);
    }

    public function dispatch($event): TreeEventInterface
    {
        foreach ($this->observerTree->getListenersForEvent($event) as $callable) {
            call_user_func($callable, $event);
        }

        return $event;
    }
}
