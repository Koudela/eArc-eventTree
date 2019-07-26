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

namespace eArc\eventTree\Transformation;

use eArc\EventTree\Exceptions\BaseException;
use eArc\EventTree\Exceptions\InvalidObserverNodeException;
use eArc\EventTree\Interfaces\Propagation\HandlerInterface;
use eArc\eventTree\Interfaces\SortableListener;
use eArc\eventTree\Interfaces\Transformation\ObserverTreeInterface;
use eArc\EventTree\Interfaces\TreeEventInterface;

class ObserverTree implements ObserverTreeInterface
{
    protected $listener = [];

    public function getListenersForEvent($event): iterable
    {
        if (!is_subclass_of($event, TreeEventInterface::class)) {
            throw new BaseException(sprintf('Event %s has to implement the %s', get_class($event), TreeEventInterface::class));
        }

        /** @var TreeEventInterface $event */
        foreach ($event->getPropagationType()->getStart() as $name) {
            $event->getTransitionInfo()->addChild($name);
        }

        foreach ($this->iterateNode($event) as $callable) {
            yield $callable;
        }

        if (0 !== $event->getTransitionChangeState() & HandlerInterface::EVENT_IS_TERMINATED) {
            return;
        }

        foreach ($event->getPropagationType()->getDestination() as $name) {
            $event->getTransitionInfo()->addChild($name);

            foreach ($this->iterateNode($event) as $callable) {
                yield $callable;
            }

            if (0 !== $event->getTransitionChangeState() & HandlerInterface::EVENT_IS_TERMINATED) {
                return;
            }
        }

        foreach ($this->iterateNodeRecursive($event, $event->getPropagationType()->getMaxDepth()) as $callable) {
            yield $callable;
        }
    }

    protected function iterateNodeRecursive(TreeEventInterface $event, int $maxDepth): iterable
    {
        foreach ($this->getSubDirNames($event->getTransitionInfo()->getCurrentPathFormatted('/')) as $name) {
            $event->getTransitionInfo()->addChild($name);

            foreach ($this->iterateNode($event) as $callable) {
                yield $callable;
            }

            if (0 !== $event->getTransitionChangeState() & HandlerInterface::EVENT_IS_TIED) {
                $isTied = true;
            }

            if ($maxDepth > 0 && 0 === $event->getTransitionChangeState() & HandlerInterface::EVENT_IS_TERMINATED) {
                foreach ($this->iterateNodeRecursive($event, $maxDepth-1) as $callable) {
                    yield $callable;
                }
            }

            $event->getTransitionInfo()->goToParent();

            if (isset($isTied)) {
                break;
            }
        }
    }

    protected function getSubDirNames(string $path): array
    {
        $dirs = [];

        foreach (di_param('earc.observer_tree.directories') as $rootDir => $rootNamespace)
        {
            chdir($rootDir);

            if (!is_dir($path)) {
                continue;
            }

            foreach (scandir('.', SCANDIR_SORT_NONE) as $fileName) {
                if ('.' !== $fileName && '..' !== $fileName && is_dir($fileName)) {
                    $dirs[] = $fileName;
                }
            }
        }

        return $dirs;
    }

    /**
     * @param TreeEventInterface $event
     *
     * @return iterable
     *
     * @throws InvalidObserverNodeException
     */
    protected function iterateNode(TreeEventInterface $event): iterable
    {
        $path = $event->getTransitionInfo()->getCurrentPathFormatted('/');
        $namespace = $event->getTransitionInfo()->getCurrentPathFormatted('\\');

        $event->setTransitionChangeState(0);

        if (!isset($this->listener[$path])) {
            $this->registerListener($path, $namespace);
        }

        foreach ($this->listener[$path] as $fQCN => $patience) {
            foreach ($event::getApplicableListener() as $base) {
                if (is_subclass_of($fQCN, $base)) {
                    yield [$fQCN => di_get($fQCN), 'process'];

                    break;
                };
            }

            if (0 !== $event->getTransitionChangeState() & HandlerInterface::EVENT_IS_FORWARDED) {
                break;
            }
        }
    }

    /**
     * @param string $path
     * @param string $namespace
     *
     * @throws InvalidObserverNodeException
     */
    protected function registerListener(string $path, string $namespace): void
    {
        $this->listener[$path] = [];
        foreach ($this->collectListener($path, $namespace) as $className => $fQCN) {
            $patience = is_subclass_of($fQCN, SortableListener::class) ? $fQCN::getPatience() : 0;
            $this->listener[$path][$fQCN] = $patience;
        }

        asort($this->listener[$path], SORT_NUMERIC);
    }

    /**
     * @param string $path
     * @param string $namespace
     *
     * @return string[]
     *
     * @throws InvalidObserverNodeException
     */
    protected static function collectListener(string $path, string $namespace): array
    {
        $listener = null;

        foreach (di_param('earc.event_tree.directories') as $rootDir => $rootNamespace)
        {
            chdir($rootDir);

            if (is_dir($path))
            {
                chdir($path);
                if (null === $listener) {
                    $listener = [];
                }
                static::processDir($rootNamespace.$namespace, $listener);
            }
        }

        if (null === $listener) {
            throw new InvalidObserverNodeException(sprintf('Path %s is no valid directory for an observer node.', $path));
        }

        return $listener;
    }

    /**
     * @param string   $namespace
     * @param string[] $listener
     */
    protected static function processDir(string $namespace, array &$listener): void
    {
        foreach (scandir('.', SCANDIR_SORT_NONE) as $fileName)
        {
            if ('.' === $fileName || '..' === $fileName || is_dir($fileName)) {
                continue;
            }

            $className = substr($fileName, 0,-4);

            $listener[$className] = $namespace.'\\'.$className;
        }
    }
}
