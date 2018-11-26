<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/earc-eventTree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Api;

use eArc\EventTree\Event\Event;
use Psr\Container\ContainerInterface;

/**
 * RootEvent is a pseudo Event that does not need any parent but can not be
 * dispatched.
 */
class RootEvent extends Event
{
    /**
     * @noinspection PhpMissingParentConstructorInspection
     *
     * @param null|ContainerInterface $container
     */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->tree = null;
        $this->start = [];
        $this->destination = [];
        $this->maxDepth = 0;
        $this->parent = null;
        $this->silence();
        $this->tie();
        $this->terminate();
    }

    /**
     * Do not call this method! The root event can not be dispatched.
     *
     * @throws \BadMethodCallException
     */
    public function dispatch(): void
    {
        throw new \BadMethodCallException();
    }


    /**
     * Transforms the tree of events to a string (for debugging purpose only).
     *
     * @return string
     */
    public function toString(): string
    {
        $str = "root:\n";
        $str .= $this->parameterToString($this);
        $str .= "  children:\n";
        $str .= $this->childrenToString($this, '    ');
        return $str;
    }

    /**
     * Transforms the parameters of a event to a string (for debugging purpose
     * only).
     *
     * @param Event $event
     * @param string $indent
     *
     * @return string
     */
    protected function parameterToString(Event $event, $indent = '  '): string
    {
        $treeIdentifier = $event->getTree() ? $event->getTree()->getIdentifier() : '';
        return
            $indent . 'tree: ' . $treeIdentifier . "\n" .
            $indent . 'start: [' . implode(', ', $event->getStart()) . "]\n" .
            $indent . 'destination: [' . implode(', ', $event->getDestination()) . "]\n" .
            $indent . 'maxDepth: ' . $event->getMaxDepth() ?? 'null' . "\n";
    }

    /**
     * Transforms the children of a event to a string (for debugging purpose
     * only).
     *
     * @param Event $parent
     * @param string $indent
     *
     * @return string
     */
    protected function childrenToString(Event $parent, $indent = '  '): string
    {
        $str = '';

        foreach ($parent->getChildren() as $name => $event)
        {
            $str .= $indent . $name . ":\n";
            $str .= $this->parameterToString($event, $indent . '  ');
            $str .= $indent . "  children:\n";
            $str .= $this->childrenToString($event, $indent . '    ');
        }

        return $str;
    }
}
