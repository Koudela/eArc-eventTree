<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/earc-eventTree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\eventTree\Debug;

use eArc\EventTree\Event;
use eArc\EventTree\Propagation\EventRouter;
use eArc\EventTree\Type;
use eArc\ObserverTree\Observer;

/**
 * The toString() functions of Node and Observer aren't an exact match for the
 * requirements of the earc event tree package. This class can help you to debug
 * events and observer trees.
 */
abstract class Debug
{

    /**
     * Transforms the tree of events to a string.
     *
     * @param Event $event
     *
     * @return string
     */
    public static function toString(Event $event): string
    {
        $str = $event->getName() . ":\n";
        $str .= self::typeToString($event->getType());
        $str .= "  children:\n";
        $str .= self::childrenToString($event, '    ');
        return $str;
    }

    /**
     * Transforms the children of a event to a string.
     *
     * @param Event $parent
     * @param string $indent
     *
     * @return string
     */
    protected static function childrenToString(Event $parent, $indent = ''): string
    {
        $str = '';

        foreach ($parent->getChildren() as $name => $event)
        {
            $str .= $indent . $name . ":\n";
            $str .= self::typeToString($event, $indent . '  ');
            $str .= $indent . "  children:\n";
            $str .= self::childrenToString($event, $indent . '    ');
        }

        return $str;
    }

    /**
     * Transforms the type of an event to a string.
     *
     * @param Type $type
     * @param string $indent
     *
     * @return string
     */
    public static function typeToString(Type $type, $indent = ''): string
    {
        $treeIdentifier = $type->getTree() ? $type->getTree()->getName() : '';
        return
            $indent . 'tree: ' . $treeIdentifier . "\n" .
            $indent . 'start: [' . implode(', ', $type->getStart()) . "]\n" .
            $indent . 'destination: [' . implode(', ', $type->getDestination()) . "]\n" .
            $indent . 'maxDepth: ' . $type->getMaxDepth() ?? 'null' . "\n";
    }



    public static function treeToString(Observer $observer, $indent = ''): string
    {
        $str = $indent . "--{$observer->getName()}--\n";
        $str .= self::listenersToString($observer, $indent . '  ');

        foreach ($observer->getChildren() as $child)
        {
            /** @var Observer $child */
            $str .= self::treeToString($child, $indent . '  ');
        }

        return $str;
    }

    /**
     * Transforms the attached listeners into a string representation.
     *
     * @param Observer $observer
     * @param string $indent
     *
     * @return string
     *
     * @throws \ReflectionException
     */
    protected static function listenersToString(Observer $observer, $indent = ''): string
    {
        $ref = new \ReflectionClass($observer);
        $propertyListener = $ref->getProperty('listener');
        $propertyListener->setAccessible(true);
        $listener = $propertyListener->getValue();
        $propertyType = $ref->getProperty('type');
        $propertyType->setAccessible(true);
        $type = $propertyType->getValue();

        $str = '';

        foreach ($listener as $FQN => $patience) {
            $str .= $indent . '  ' . $FQN . ': ' . '{ patience: $patience, type: { '
                . self::eventPhasesToString($type[$FQN]) . " } }\n";
        }

        return $str;
    }

    /**
     * Transforms the eventPhases to a string representation.
     *
     * @param int $eventPhases
     *
     * @return string
     */
    public static function eventPhasesToString(int $eventPhases): string
    {
        if (EventRouter::PHASE_ACCESS === $eventPhases) {
            return 'access';
        }

        $arr = [];

        if (EventRouter::PHASE_START & $eventPhases) {
            $arr[] = 'start';
        }

        if (EventRouter::PHASE_BEFORE & $eventPhases) {
            $arr[] = 'before';
        }

        if (EventRouter::PHASE_DESTINATION & $eventPhases) {
            $arr[] = 'destination';
        }

        if (EventRouter::PHASE_BEYOND & $eventPhases) {
            $arr[] = 'beyond';
        }

        return implode(' | ', $arr);
    }
}
