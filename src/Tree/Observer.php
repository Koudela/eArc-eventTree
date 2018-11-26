<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/earc-eventTree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Tree;

use eArc\EventTree\Api\Interfaces\EventListenerInterface;
use eArc\EventTree\Event\Event;
use Psr\Container\ContainerInterface;

/**
 * Observer defines the listenable nature of the composite classes
 */
class Observer extends Node
{
    /** @var array */
    protected $initialisedListener = [];

    /** @var array */
    protected $listener = [];

    /** @var array */
    protected $eventPhases = [];

    /**
     * Calls all registered listeners that match the event phase sorted by their
     * patience until either all are called or the event is silenced.
     *
     * @param Event $event
     * @param int $eventPhase
     */
    public function callListeners(Event $event, int $eventPhase): void
    {
        asort($this->listener, SORT_NUMERIC);

        foreach($this->listener as $FQN => $patience)
        {
            if ($event->isSilenced())
            {
                $event->endSilence();
                break;
            }

            if (0 !== ($this->eventPhases[$FQN] & $eventPhase))
            {
                $this->getListener($event->getContainer(), $FQN)->processEvent($event);
            }
        }
    }

    /**
     * Get the listener from the container or if it fails try to build the
     * class. (A class not part of the container is always build only once.)
     *
     * @param null|ContainerInterface $container
     * @param string $FQN
     *
     * @return EventListenerInterface
     */
    protected function getListener(?ContainerInterface $container, string $FQN): EventListenerInterface
    {
        if ($container && $container->has($FQN))
        {
            return $container->get($FQN);
        }

        if (!isset($this->initialisedListener[$FQN]))
        {
            $this->initialisedListener[$FQN] = new $FQN();
        }

        return $this->initialisedListener[$FQN];
    }

    /**
     * Registers a listener by its fully qualified class name or its container
     * name.
     *
     * @param string $FQN
     * @param int $eventPhases
     * @param float $patience
     */
    public function registerListener(string $FQN, int $eventPhases = EventRouter::PHASE_ACCESS, float $patience = 0): void
    {
        $this->listener[$FQN] = $patience;
        $this->eventPhases[$FQN] = $eventPhases;
    }

    /**
     * Unregisters a listener by its fully qualified class name or its container
     * name. (It must be the same name the listener was registered.)
     *
     * @param string $FQN
     */
    public function unregisterListener(string $FQN): void
    {
        unset($this->initialisedListener[$FQN]);
        unset($this->listener[$FQN]);
        unset($this->eventPhases[$FQN]);
    }

    /**
     * @inheritdoc
     */
    public function toString(): string
    {
        return $this->root->nodeToString();
    }

    /**
     * @inheritdoc
     */
    protected function nodeToString($indent = ''): string
    {
        $str = $indent . "--{$this->name}--\n";
        $str .= $this->listenersToString($indent . '  ');

        foreach ($this->children as $child)
        {
            /** @var Observer $child */
            $str .= $child->nodeToString($indent . '  ');
        }

        return $str;
    }

    /**
     * Transforms the attached listeners into a string representation.
     *
     * @param string $indent
     *
     * @return string
     */
    protected function listenersToString($indent = '  '): string
    {
        $str = '';

        foreach ($this->listener as $FQN => $patience)
        {
            $str .= $indent . '  ' . $FQN . ': '
                . "{ patience: $patience, type: "
                . EventRouter::eventPhasesToString($this->eventPhases[$FQN])
                . " }\n";
        }

        return $str;
    }
}
