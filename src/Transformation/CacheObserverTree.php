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

use eArc\EventTree\Exceptions\InvalidObserverNodeException;
use eArc\EventTree\Interfaces\ParameterInterface;
use eArc\EventTree\Interfaces\TreeEventInterface;

class CacheObserverTree extends AbstractObserverTree implements ParameterInterface
{
    /** @var array */
    protected $eventTreeArray;
    /** @var array */
    protected $currentEventTree;

    public function __construct()
    {
        $file = di_param(ParameterInterface::CACHE_FILE, '/tmp/earc_event_tree_cache.php');

        if (!is_file($file)) {
            exec(__DIR__.'/../../tools/build-cache '.di_param(ParameterInterface::CONFIG_FILE));
        }

        $this->eventTreeArray = include $file;
    }

    protected function getListenerStack(TreeEventInterface $event): array
    {
        return $this->currentEventTree['l'];
    }

    /**
     * @param TreeEventInterface $event
     * @param string             $name
     *
     * @throws InvalidObserverNodeException
     */
    protected function addChild(TreeEventInterface $event, string $name): void
    {
        if (!isset($this->currentEventTree['s'][$name])) {
            throw new InvalidObserverNodeException(sprintf(
                '{1694cff8-4582-4e66-bc6c-0a33a141abdd} Path `%s` is no valid directory for an observer node.',
                implode('/', $event->getTransitionInfo()->getCurrentPath()).'/'.$name
            ));
        }
        $event->getTransitionInfo()->addChild($name, $name);
        $this->currentEventTree = $this->currentEventTree['s'][$name];

    }

    protected function getAvailableLeafs(TreeEventInterface $event): array
    {
        return array_keys($this->currentEventTree['s']);
    }

    protected function resetTreeState(): void
    {
        $this->currentEventTree = $this->eventTreeArray;
    }
}
