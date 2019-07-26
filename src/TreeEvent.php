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

use eArc\EventTree\Exceptions\IsDispatchedException;
use eArc\EventTree\Exceptions\IsNotDispatchedException;
use eArc\EventTree\Interfaces\Propagation\HandlerInterface;
use eArc\EventTree\Interfaces\Propagation\PropagationTypeInterface;
use eArc\eventTree\Interfaces\Transformation\TransitionInfoInterface;
use eArc\EventTree\Interfaces\TreeEventInterface;
use eArc\EventTree\Propagation\Handler;
use eArc\EventTree\Propagation\PropagationType;
use eArc\eventTree\Transformation\TransitionInfo;
use eArc\Observer\Interfaces\ListenerInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Adds the dispatch method and the factory getters.
 */
class TreeEvent implements StoppableEventInterface, TreeEventInterface
{
    /** @var int */
    protected $transitionChangeState;

    /** @var HandlerInterface|null */
    protected $handler;

    /** @var PropagationTypeInterface */
    protected $propagationType;

    /** @var TransitionInfoInterface */
    protected $transitionInfo;

    /**
     * @param PropagationType $propagationType
     */
    public function __construct(PropagationType $propagationType)
    {
        $this->propagationType = $propagationType;
        $this->transitionInfo = di_is_decorated(TransitionInfoInterface::class)
            ? di_get(TransitionInfoInterface::class)
            : di_get(TransitionInfo::class);
    }

    public function dispatch(): void
    {
        if (null !== $this->handler) {
            throw new IsDispatchedException('This event has been dispatched already.');
        }

        $this->handler = new Handler($this);
        $this->propagationType->getDispatcher()->dispatch($this);
    }

    public function getHandler(): HandlerInterface
    {
        if (null === $this->handler) {
            throw new IsNotDispatchedException('The event has not been dispatched yet.');
        }

        return $this->handler;
    }

    public function getPropagationType(): PropagationTypeInterface
    {
        return $this->propagationType;
    }

    public function getTransitionInfo(): TransitionInfoInterface
    {
        return $this->transitionInfo;
    }

    public function getTransitionChangeState(): int
    {
        return $this->transitionChangeState;
    }

    public function setTransitionChangeState(int $state): void
    {
        $this->transitionChangeState = $this->transitionChangeState | $state;
    }

    public function isPropagationStopped(): bool
    {

        return 7 === $this->transitionChangeState;
    }

    public static function getApplicableListener(): array
    {
        return [ListenerInterface::class];
    }
}
