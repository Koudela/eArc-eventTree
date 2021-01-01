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

namespace eArc\EventTree\Propagation;

use eArc\EventTree\Interfaces\Propagation\HandlerInterface;
use eArc\EventTree\Interfaces\TreeEventInterface;

/**
 * Defines a three dimensional boolean state the event is in.
 */
class Handler implements HandlerInterface
{
    /** @var TreeEventInterface */
    protected $event;

    public function __construct(TreeEventInterface $event)
    {
        $this->event = $event;
    }

    public function forward(): HandlerInterface
    {
        $this->event->setTransitionChangeState($this->event->getTransitionChangeState() | self::EVENT_IS_FORWARDED);

        return $this;
    }

    public function tie(): HandlerInterface
    {
        $this->event->setTransitionChangeState($this->event->getTransitionChangeState() | self::EVENT_IS_TIED);

        return $this;
    }

    public function terminate(): HandlerInterface
    {
        $this->event->setTransitionChangeState($this->event->getTransitionChangeState() | self::EVENT_IS_TERMINATED);

        return $this;
    }

    public function kill(): HandlerInterface
    {
        $this->event->setTransitionChangeState(self::EVENT_IS_TERMINATED | self::EVENT_IS_TIED | self::EVENT_IS_FORWARDED);

        return $this;
    }
}
