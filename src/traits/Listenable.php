<?php

namespace eArc\eventTree\traits;

use Psr\Container\ContainerInterface;
use eArc\eventTree\Interfaces\EventListener;
use eArc\eventTree\Tree\EventRouter;

trait Listenable
{
    protected $listener = [];

    public function dispatchEvent(EventRouter $eventRouter): void
    {
        $event = $eventRouter->getEvent();

        sort($this->listener);

        foreach($this->listener as $FQN => $priority)
        {
            if ($event->isSilenced())
            {
                $event->endSilence();
                break;
            }

            $this->getListener($event->getContainer(), $FQN)->processEvent($event);
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

    public function registerListener(string $FQN, int $patience): void
    {
        $this->listener[$FQN] = $patience;
    }

    public function unRegisterListener(string $FQN): void
    {
        unset($this->listener[$FQN]);
    }
}
