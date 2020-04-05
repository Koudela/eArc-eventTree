<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * event tree component
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-eventTree/
 * @copyright Copyright (c) 2018-2020 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Transformation;

use eArc\EventTree\Exceptions\BaseException;
use eArc\EventTree\Exceptions\InvalidObserverNodeException;
use eArc\EventTree\Interfaces\ParameterInterface;
use eArc\EventTree\Interfaces\PhaseSpecificListenerInterface;
use eArc\EventTree\Interfaces\Propagation\HandlerInterface;
use eArc\EventTree\Interfaces\SortableListenerInterface;
use eArc\EventTree\Interfaces\Transformation\ObserverTreeInterface;
use eArc\EventTree\Interfaces\TreeEventInterface;
use eArc\EventTree\Util\CompositeDir;
use eArc\EventTree\Util\DirectiveReader;

class ObserverTree implements ObserverTreeInterface, ParameterInterface
{
    protected $listener = [];
    protected $blacklistedListener;

    public function __construct()
    {
        $this->blacklistedListener = di_param(ParameterInterface::BLACKLIST, []);
    }

    /**
     * @param TreeEventInterface $event
     *
     * @return iterable
     *
     * @throws BaseException
     * @throws InvalidObserverNodeException
     */
    public function getListenersForEvent($event): iterable
    {
        if (!is_subclass_of($event, TreeEventInterface::class)) {
            throw new BaseException(sprintf('Event %s has to implement the %s', get_class($event), TreeEventInterface::class));
        }

        /** @var TreeEventInterface $event */
        foreach ($event->getPropagationType()->getStart() as $name) {
            $this->addChild($event, $name);
        }

        foreach ($this->iterateNode($event, self::PHASE_START) as $callable) {
            yield $callable;
        }

        if (0 !== ($event->getTransitionChangeState() & HandlerInterface::EVENT_IS_TERMINATED)) {
            return;
        }

        $lastKey = count($event->getPropagationType()->getDestination()) - 1;

        foreach ($event->getPropagationType()->getDestination() as $key => $name) {
            $this->addChild($event,$name);

            foreach ($this->iterateNode($event, $lastKey !== $key ? self::PHASE_BEFORE : self::PHASE_DESTINATION) as $callable) {
                yield $callable;
            }

            if (0 !== ($event->getTransitionChangeState() & HandlerInterface::EVENT_IS_TERMINATED)) {
                return;
            }
        }

        $event->setTransitionChangeState(0);
        $maxDepth = $event->getPropagationType()->getMaxDepth();

          // Deep Search
//        foreach ($this->iterateNodeRecursive($event, $this->getNextMaxDepth($maxDepth)) as $callable) {
//            yield $callable;
//        }

        //WideSearch
        $pathQueue = [$event->getTransitionInfo()->getCurrentPath()];

        while (!empty($pathQueue) && (null === $maxDepth || $maxDepth > 0)) {
            foreach ($this->walkTreeLevel($event, $pathQueue) as $callable) {
                yield $callable;
            }

            $maxDepth = $this->getNextMaxDepth($maxDepth);
        }
    }

    /**
     * @param TreeEventInterface $event
     * @param string $name
     *
     * @throws InvalidObserverNodeException
     */
    protected function addChild(TreeEventInterface $event, string $name): void
    {
        $path = $event->getTransitionInfo()->getCurrentRealPath();
        $redirectDirective = DirectiveReader::getRedirect($path);
        $newPath = $redirectDirective->getPathForLeaf($name);
        $event->getTransitionInfo()->addChild($name, $newPath);
    }

    /**
     * @param TreeEventInterface $event
     * @param array              $pathQueue
     *
     * @return iterable
     *
     * @throws InvalidObserverNodeException
     */
    protected function walkTreeLevel(TreeEventInterface $event, array &$pathQueue): iterable
    {
        $transitionInfo = $event->getTransitionInfo();

        $newPathQueue = [];

        foreach ($pathQueue as $path) {
            $this->goToTreeNode($event, $path);

            foreach (CompositeDir::getSubDirNames($transitionInfo->getCurrentRealPath()) as $name) {
                $this->addChild($event, $name);

                foreach ($this->iterateNode($event, self::PHASE_BEYOND) as $callable) {
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

    /**
     * @param TreeEventInterface $event
     * @param array $path
     *
     * @throws InvalidObserverNodeException
     */
    protected function goToTreeNode(TreeEventInterface $event, array $path)
    {
        $transitionInfo = $event->getTransitionInfo();

        $cnt = count($transitionInfo->getCurrentPath());

        for ($i = 0; $i < $cnt; $i++) {
            $transitionInfo->goToParent();
        }

        foreach ($path as $name) {
            $this->addChild($event, $name);
        }
    }

    /**
     * @param TreeEventInterface $event
     * @param int|null $maxDepth
     *
     * @return iterable
     *
     * @throws InvalidObserverNodeException
     */
    protected function iterateNodeRecursive(TreeEventInterface $event, ?int $maxDepth): iterable
    {
        if ($maxDepth < 0) {
            return;
        }

        foreach (CompositeDir::getSubDirNames($event->getTransitionInfo()->getCurrentRealPath()) as $name) {
            $this->addChild($event, $name);

            foreach ($this->iterateNode($event, self::PHASE_BEYOND) as $callable) {
                yield $callable;
            }

            if (0 !== ($event->getTransitionChangeState() & HandlerInterface::EVENT_IS_TIED)) {
                $isTied = true;
            }

            if ((null === $maxDepth || $maxDepth > 0) && 0 === ($event->getTransitionChangeState() & HandlerInterface::EVENT_IS_TERMINATED)) {
                foreach ($this->iterateNodeRecursive($event, $this->getNextMaxDepth($maxDepth)) as $callable) {
                    yield $callable;
                }
            }

            $event->getTransitionInfo()->goToParent();

            if (isset($isTied)) {
                break;
            }
        }
    }

    protected function getNextMaxDepth(?int $maxDepth)
    {
        return null === $maxDepth ? null : $maxDepth -1;
    }

    /**
     * @param TreeEventInterface $event
     * @param int                $phase
     *
     * @return iterable
     *
     * @throws InvalidObserverNodeException
     */
    protected function iterateNode(TreeEventInterface $event, int $phase = self::PHASE_ACCESS): iterable
    {
        $path = $event->getTransitionInfo()->getCurrentRealPath();

        $event->setTransitionChangeState(0);

        if (!isset($this->listener[$path])) {
            $this->registerListener($path);
        }

        foreach ($this->listener[$path] as $fQCN => $patience) {
            foreach ($event::getApplicableListener() as $base) {
                if (is_subclass_of($fQCN, $base) && $this->inPhase($fQCN, $phase)) {
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
     * @param string $fQCN
     * @param int    $phase
     *
     * @return bool
     */
    protected function inPhase(string $fQCN, int $phase = self::PHASE_ACCESS): bool
    {
        if (!is_subclass_of($fQCN, PhaseSpecificListenerInterface::class)) {
            return true;
        }

        /** @var PhaseSpecificListenerInterface $fQCN */
        return 0 !== ($fQCN::getPhase() & $phase);

    }

    /**
     * @param string $path
     *
     * @throws InvalidObserverNodeException
     */
    protected function registerListener(string $path): void
    {
        $this->listener[$path] = [];
        foreach (CompositeDir::collectListener($path) as $className => $fQCN) {
            if (!isset($this->blacklistedListener[$fQCN]) || !$this->blacklistedListener[$fQCN]) {
                $patience = is_subclass_of($fQCN, SortableListenerInterface::class) ? $fQCN::getPatience() : 0;
                $this->listener[$path][$fQCN] = $patience;
            }
        }

        asort($this->listener[$path], SORT_NUMERIC);
    }
}
