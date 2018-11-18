<?php

namespace eArc\eventTree\Traits;

use Psr\Container\ContainerInterface;
use eArc\eventTree\Interfaces\EventListener;
use eArc\eventTree\Tree\EventRouter;

trait Listenable
{
    protected $initialisedListener = [];
    protected $listener = [];
    protected $eventPhases = [];

    public function callListeners(EventRouter $eventRouter): void
    {
        $event = $eventRouter->getEvent();

        asort($this->listener, SORT_NUMERIC);

        foreach($this->listener as $FQN => $patience)
        {
            if ($event->isSilenced())
            {
                $event->endSilence();
                break;
            }

            if ($eventRouter->matchesEventPhase($this->eventPhases[$FQN]))
            {
                $this->getListener($event->getContainer(), $FQN)->processEvent($event);
            }
        }

        $eventRouter->nextLeaf();
    }

    protected function getListener(?ContainerInterface $container, string $FQN): EventListener
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

    public function registerListener(string $FQN, int $eventPhases = EventRouter::PHASE_ACCESS, float $patience = 0): void
    {
        $this->listener[$FQN] = $patience;
        $this->eventPhases[$FQN] = $eventPhases;
    }

    public function unregisterListener(string $FQN): void
    {
        unset($this->initialisedListener[$FQN]);
        unset($this->listener[$FQN]);
        unset($this->eventPhases[$FQN]);
    }
}
