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

namespace eArc\EventTree;

use eArc\EventTree\Interfaces\TreeEventRouterInterface;
use eArc\EventTree\Interfaces\HandlerInterface;

/**
 * Defines a three dimensional boolean state the event is in.
 */
class Handler implements HandlerInterface
{
    const EVENT_IS_SILENCED = 1;
    const EVENT_IS_TIED = 2;
    const EVENT_IS_TERMINATED = 4;

    /** @var TreeEventRouterInterface */
    protected $eventRouter;

    public function __construct(TreeEventRouterInterface $eventRouter)
    {
        $this->eventRouter = $eventRouter;
    }

    /**
     * @inheritdoc
     */
    public function silence(): void
    {
        $this->eventRouter->setState(self::EVENT_IS_SILENCED);
    }

    /**
     * @inheritdoc
     */
    public function tie(): void
    {
        $this->eventRouter->setState(self::EVENT_IS_TIED);
    }

    /**
     * @inheritdoc
     */
    public function terminate(): void
    {
        $this->eventRouter->setState(self::EVENT_IS_TERMINATED);
    }

    /**
     * @inheritdoc
     */
    public function kill(): void
    {
        $this->eventRouter->setState(
            self::EVENT_IS_TIED | self::EVENT_IS_TERMINATED
        );
    }
}
