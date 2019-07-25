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

    /**
     * @inheritdoc
     */
    public function forward(): void
    {
        $this->event->setTransitionChangeState(self::EVENT_IS_FORWARDED);
    }

    /**
     * @inheritdoc
     */
    public function tie(): void
    {
        $this->event->setTransitionChangeState(self::EVENT_IS_TIED);
    }

    /**
     * @inheritdoc
     */
    public function terminate(): void
    {
        $this->event->setTransitionChangeState(self::EVENT_IS_TERMINATED);
    }
}
