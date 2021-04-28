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

namespace eArc\EventTree\Transformation;

use eArc\EventTree\Exceptions\EventIsTerminatedException;
use eArc\EventTree\Exceptions\UnsuitableEventException;
use eArc\EventTree\Interfaces\ParameterInterface;
use eArc\EventTree\Interfaces\Propagation\HandlerInterface;
use eArc\EventTree\Interfaces\Transformation\ObserverTreeInterface;
use eArc\EventTree\Interfaces\TreeEventInterface;

abstract class AbstractObserverTree implements ObserverTreeInterface, ParameterInterface
{
    /**
     * @inheritDoc
     */
    public function getListenersForEvent($event): iterable
    {
        if (!is_subclass_of($event, TreeEventInterface::class)) {
            throw new UnsuitableEventException(sprintf('{669d4d72-cfd7-477b-a87a-a2d476c67b97} Event %s has to implement the %s', get_class($event), TreeEventInterface::class));
        }

        $this->resetTreeState();

        try {
            foreach ($this->yieldStart($event) as $callable) {
                yield $callable;
            }

            foreach ($this->yieldDestination($event) as $callable) {
                yield $callable;
            }

            foreach ($this->yieldBeyond($event) as $callable) {
                yield $callable;
            }

        } catch (EventIsTerminatedException $exception) {
            return;
        }
    }

    protected abstract function getListenerStack(TreeEventInterface $event): array;

    protected abstract function addChild(TreeEventInterface $event, string $name): void;

    protected abstract function getAvailableLeafs(TreeEventInterface $event): array;

    protected abstract function resetTreeState(): void;

    /**
     * @param TreeEventInterface $event
     *
     * @return iterable
     *
     * @throws EventIsTerminatedException
     */
    protected function yieldStart(TreeEventInterface $event): iterable
    {
        foreach ($event->getPropagationType()->getStart() as $name) {
            $this->addChild($event, $name);
        }

        $phase = !empty($event->getPropagationType()->getDestination()) ?
            self::PHASE_START :
            self::PHASE_START | self::PHASE_BEFORE | self::PHASE_DESTINATION;

        foreach ($this->yieldListener($event, $phase) as $callable) {
            yield $callable;
        }

        $this->checkTerminated($event);
    }

    /**
     * @param TreeEventInterface $event
     *
     * @return iterable
     *
     * @throws EventIsTerminatedException
     */
    protected function yieldDestination(TreeEventInterface $event): iterable
    {
        $lastKey = count($event->getPropagationType()->getDestination()) - 1;

        foreach ($event->getPropagationType()->getDestination() as $key => $name) {
            $this->addChild($event,$name);

            foreach ($this->yieldListener($event, $lastKey !== $key ? self::PHASE_BEFORE : (0 === $lastKey ? self::PHASE_BEFORE | self::PHASE_DESTINATION : self::PHASE_DESTINATION)) as $callable) {
                yield $callable;
            }

            $this->checkTerminated($event);
        }
    }

    protected function yieldBeyond(TreeEventInterface $event): iterable
    {
        $event->setTransitionChangeState(HandlerInterface::EVENT_IS_CLEAN);

        $maxDepth = $event->getPropagationType()->getMaxDepth();

        //WideSearch
        $pathQueue = [$event->getTransitionInfo()->getCurrentPath()];

        while (!empty($pathQueue) && (null === $maxDepth || $maxDepth > 0)) {
            foreach ($this->yieldTreeLevel($event, $pathQueue) as $callable) {
                yield $callable;
            }

            $maxDepth = $this->getNextMaxDepth($maxDepth);
        }
    }

    protected function yieldTreeLevel(TreeEventInterface $event, array &$pathQueue): iterable
    {
        $transitionInfo = $event->getTransitionInfo();

        $newPathQueue = [];

        foreach ($pathQueue as $path) {
            $this->goToTreeNode($event, $path);

            foreach ($this->getAvailableLeafs($event) as $name) {
                $this->addChild($event, $name);

                foreach ($this->yieldListener($event, self::PHASE_BEYOND) as $callable) {
                    yield $callable;
                }

                if (0 !== ($event->getTransitionChangeState() & HandlerInterface::EVENT_IS_TIED)) {
                    $event->setTransitionChangeState($event->getTransitionChangeState() - HandlerInterface::EVENT_IS_TIED);
                    $newPathQueue = [$transitionInfo->getCurrentPath()];

                    break 2;
                }

                if (0 === ($event->getTransitionChangeState() & HandlerInterface::EVENT_IS_TERMINATED)) {
                    $newPathQueue[] = $transitionInfo->getCurrentPath();
                }

                $transitionInfo->goToParent();
            }
        }

        $pathQueue = $newPathQueue;

    }

    protected function goToTreeNode(TreeEventInterface $event, array $path): void
    {
        $transitionInfo = $event->getTransitionInfo();

        $cnt = count($transitionInfo->getCurrentPath());

        for ($i = 0; $i < $cnt; $i++) {
            $transitionInfo->goToParent();
        }

        $this->resetTreeState();

        foreach ($path as $name) {
            $this->addChild($event, $name);
        }
    }

    /**
     * @param TreeEventInterface $event
     * @param int                $phaseEvent
     *
     * @return iterable
     */
    protected function yieldListener(TreeEventInterface $event, int $phaseEvent = self::PHASE_ACCESS): iterable
    {
        $listener = $this->getListenerStack($event);

        $event->setTransitionChangeState(HandlerInterface::EVENT_IS_CLEAN);

        foreach ($listener as $fQCN => $phaseClass) {
            if (!$this->inPhase($phaseClass, $phaseEvent)) {
                continue;
            }

            foreach ($event::getApplicableListener() as $base) {
                if (is_subclass_of($fQCN, $base)) {
                    yield [di_get($fQCN), 'process'];

                    break;
                };
            }

            if (0 !== ($event->getTransitionChangeState() & HandlerInterface::EVENT_IS_FORWARDED)) {
                $event->setTransitionChangeState($event->getTransitionChangeState() - HandlerInterface::EVENT_IS_FORWARDED);
                break;
            }
        }
    }

    /**
     * @param TreeEventInterface $event
     *
     * @throws EventIsTerminatedException
     */
    protected function checkTerminated(TreeEventInterface $event) : void
    {
        if (0 !== ($event->getTransitionChangeState() & HandlerInterface::EVENT_IS_TERMINATED)) {
            throw new EventIsTerminatedException();
        }
    }

    protected function inPhase(int $phaseClass, int $phaseEvent = self::PHASE_ACCESS): bool
    {
        return 0 !== ($phaseClass & $phaseEvent);
    }

    protected function getNextMaxDepth(?int $maxDepth): ?int
    {
        return null === $maxDepth ? null : $maxDepth -1;
    }
}
