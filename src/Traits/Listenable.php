<?php

namespace eArc\eventTree\Traits;

use Psr\Container\ContainerInterface;
use eArc\eventTree\Interfaces\EventListener;
use eArc\eventTree\Tree\EventRouter;

trait Listenable
{
    protected $listener = [];
    protected $type = [];

    public function dispatchEvent(EventRouter $eventRouter): void
    {
        $event = $eventRouter->getEvent();
        $eventPhase = $eventRouter->getEventPhase();

        sort($this->listener);

        foreach($this->listener as $FQN => $patience)
        {
            if ($event->isSilenced())
            {
                $event->endSilence();
                break;
            }

            if ($this->type[$FQN] === 'access' || $this->type[$FQN] === $eventPhase)
            {
                $this->getListener($event->getContainer(), $FQN)->processEvent($event);
            }
        }

        if ($nextLeaf = $eventRouter->next())
        {
            $nextLeaf->dispatchEvent($eventRouter);
        }
    }

    protected function getListener(?ContainerInterface $container, string $FQN): EventListener
    {
        if ($container && $container->has($FQN))
        {
            return $container->get($FQN);
        }

        return new $FQN();
    }

    public function registerListener(string $FQN, string $type, int $patience): void
    {
        $this->listener[$FQN] = $patience;
        $this->type[$FQN] = $type;
    }

    public function unRegisterListener(string $FQN): void
    {
        unset($this->listener[$FQN]);
        unset($this->type[$FQN]);
    }
}
