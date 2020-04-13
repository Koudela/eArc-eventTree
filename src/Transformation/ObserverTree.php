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

use eArc\EventTree\Exceptions\InvalidObserverNodeException;
use eArc\EventTree\Interfaces\TreeEventInterface;
use eArc\EventTree\Util\CompositeDir;
use eArc\EventTree\Util\DirectiveReader;

class ObserverTree extends AbstractObserverTree
{
    /**
     * @param TreeEventInterface $event
     *
     * @return array
     *
     * @throws InvalidObserverNodeException
     */
    protected function getListenerStack(TreeEventInterface $event): array
    {
        $path = $event->getTransitionInfo()->getCurrentRealPath();

        return di_get(CompositeDir::class)->collectListener($path);
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

    protected function getAvailableLeafs(TreeEventInterface $event): array
    {
        return CompositeDir::getSubDirNames($event->getTransitionInfo()->getCurrentRealPath());
    }

    protected function resetTreeState(): void
    {
    }
}
