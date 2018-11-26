<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/earc-eventTree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Event;

use eArc\EventTree\Api\Interfaces\EventFactoryInterface;
use eArc\EventTree\Transformation\EventFactory;
use eArc\EventTree\Tree\ObserverRoot;
use Psr\Container\ContainerInterface;

/**
 *
 */
class Event extends PropagatableEventHandler
{

    public function __construct(
        ?Event $parent = null,
        ?ObserverRoot $tree = null,
        array $start = [],
        array $destination = [],
        ?int $maxDepth = 0,
        bool $inheritPayload = false,
        ?ContainerInterface $container = null
    ) {
        parent::__construct(
            $parent,
            $tree,
            $start,
            $destination,
            $maxDepth,
            $inheritPayload,
            $container
        );
    }

    public function getEventFactoryFromRoot(): EventFactory
    {
        return new EventFactory($this->getRoot());
    }

    public function getEventFactory(): EventFactory
    {
        return new EventFactory($this);
    }

}
