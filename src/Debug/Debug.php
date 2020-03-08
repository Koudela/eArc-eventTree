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

namespace eArc\EventTree\Debug;

use eArc\EventTree\Interfaces\Propagation\PropagationTypeInterface;
use eArc\eventTree\Interfaces\Transformation\ObserverTreeInterface;

/**
 * This class can help you to debug events and observer trees.
 */
abstract class Debug
{
    /**
     * Transforms the propagation type of an tree event to a string..
     *
     * @param PropagationTypeInterface $type
     * @param string                   $indent
     *
     * @return string
     */
    public static function PropagationTypeToString(PropagationTypeInterface $type, $indent = ''): string
    {
        return
            $indent.'dispatcher:'.di_static(get_class($type->getDispatcher()))."\n" .
            $indent.'start: ['.implode(', ', $type->getStart())."]\n" .
            $indent.'destination: ['.implode(', ', $type->getDestination())."]\n" .
            $indent.'maxDepth: '.$type->getMaxDepth() ?? 'null'."\n";
    }

    /**
     * @param ObserverTreeInterface $observer
     * @param string $indent
     *
     * @return string
     */
    public static function treeToString(ObserverTreeInterface $observer, $indent = ''): string
    {
        $str = $indent . "--{$observer->getName()}--\n";
        $str .= self::listenersToString($observer, $indent . '  ');

        foreach ($observer->getChildren() as $child)
        {
            $str .= self::treeToString($child, $indent . '  ');
        }

        return $str;
    }

    /**
     * Transforms the attached listeners into a string representation.
     *
     * @param ObserverTreeInterface $observer
     * @param string $indent
     *
     * @return string
     */
    protected static function listenersToString(ObserverTreeInterface $observer, $indent = ''): string
    {
        $listener = (function() {return $this->listenerPatience;})->call($observer);

        $str = '';

        foreach ($listener as $fQCN => $patience) {
            /** @noinspection PhpUndefinedMethodInspection */
            $str .= $indent . '  ' . $fQCN . ': ' . '{ patience: $patience, type: { '
                . self::eventPhasesToString($fQCN::getTypes()) . " } }\n";
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
        if (TreeEventRouterInterface::PHASE_ACCESS === $eventPhases) {
            return 'access';
        }

        $arr = [];

        if (TreeEventRouterInterface::PHASE_START & $eventPhases) {
            $arr[] = 'start';
        }

        if (TreeEventRouterInterface::PHASE_BEFORE & $eventPhases) {
            $arr[] = 'before';
        }

        if (TreeEventRouterInterface::PHASE_DESTINATION & $eventPhases) {
            $arr[] = 'destination';
        }

        if (TreeEventRouterInterface::PHASE_BEYOND & $eventPhases) {
            $arr[] = 'beyond';
        }

        return implode(' | ', $arr);
    }
}
