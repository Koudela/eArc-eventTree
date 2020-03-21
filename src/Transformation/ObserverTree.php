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
use eArc\EventTree\Interfaces\PhaseSpecificListenerInterface;
use eArc\EventTree\Interfaces\Propagation\HandlerInterface;
use eArc\EventTree\Interfaces\SortableListenerInterface;
use eArc\EventTree\Interfaces\Transformation\ObserverTreeInterface;
use eArc\EventTree\Interfaces\TreeEventInterface;
use eArc\EventTree\Util\CompositeDir;

class ObserverTree implements ObserverTreeInterface
{
    protected static $listener = [];
    protected $blacklistedListener;

    public function __construct()
    {
        $this->blacklistedListener = di_param('earc.event_tree.blacklist', []);
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
            $event->getTransitionInfo()->addChild($name);
        }

        foreach ($this->iterateNode($event, self::PHASE_START) as $callable) {
            yield $callable;
        }

        if (0 !== ($event->getTransitionChangeState() & HandlerInterface::EVENT_IS_TERMINATED)) {
            return;
        }

        $lastKey = count($event->getPropagationType()->getDestination()) - 1;

        foreach ($event->getPropagationType()->getDestination() as $key => $name) {
            $event->getTransitionInfo()->addChild($name);

            foreach ($this->iterateNode($event, $lastKey !== $key ? self::PHASE_BEFORE : self::PHASE_DESTINATION) as $callable) {
                yield $callable;
            }

            if (0 !== ($event->getTransitionChangeState() & HandlerInterface::EVENT_IS_TERMINATED)) {
                return;
            }
        }

        $maxDepth = $this->getNextMaxDepth($event->getPropagationType()->getMaxDepth());

        foreach ($this->iterateNodeRecursive($event, $maxDepth) as $callable) {
            yield $callable;
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

        foreach (CompositeDir::getSubDirNames($event->getTransitionInfo()->getCurrentPathFormatted('/')) as $name) {
            $event->getTransitionInfo()->addChild($name);

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
        $path = './'.$event->getTransitionInfo()->getCurrentPathFormatted('/');
        $namespace = $event->getTransitionInfo()->getCurrentPathFormatted('\\');

        $event->setTransitionChangeState(0);

        if (!isset(self::$listener[$path])) {
            $this->registerListener($path, $namespace);
        }

        foreach (self::$listener[$path] as $fQCN => $patience) {
            foreach ($event::getApplicableListener() as $base) {
                if (is_subclass_of($fQCN, $base) && $this->inPhase($fQCN, $phase)) {
                    yield [di_get($fQCN), 'process'];

                    break;
                };
            }

            if (0 !== ($event->getTransitionChangeState() & HandlerInterface::EVENT_IS_FORWARDED)) {
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
     * @param string $namespace
     *
     * @throws InvalidObserverNodeException
     */
    protected function registerListener(string $path, string $namespace): void
    {
        self::$listener[$path] = [];
        foreach (CompositeDir::collectListener($path, $namespace) as $className => $fQCN) {
            if (!isset($this->blacklistedListener[$fQCN])) {
                $patience = is_subclass_of($fQCN, SortableListenerInterface::class) ? $fQCN::getPatience() : 0;
                self::$listener[$path][$fQCN] = $patience;
            }
        }

        asort(self::$listener[$path], SORT_NUMERIC);
    }

}
