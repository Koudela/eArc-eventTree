<?php

namespace eArc\eventTree\Event;

use eArc\eventTree\Interfaces\EventHandler;
use eArc\eventTree\Interfaces\EventType;
use eArc\eventTree\Interfaces\PropagationType;
use Psr\Container\ContainerInterface;

abstract class Event extends PayloadContainer implements EventType, PropagationType, EventHandler
{
    protected $parent;
    protected $children = [];
    protected $isSilenced = false;
    protected $isSelfTerminated = false;
    protected $areOthersTerminated = false;

    public function __construct(Event $parent, ?ContainerInterface $container = null)
    {
        parent::__construct($container);
        $this->parent = $parent;
    }

    public function silencePropagation(): void
    {
        $this->isSilenced = true;
    }

    public function terminateSelf(): void
    {
        $this->isSelfTerminated = true;
    }

    public function endSilence(): void
    {
        $this->isSilenced = false;
    }

    public function isSilenced(): bool
    {
        return $this->isSilenced;
    }

    public function isSelfTerminated(): bool
    {
        return $this->isSelfTerminated;
    }

    public function dispatchNewEvent(string $FQN): Event
    {
        $event = new $FQN($this);

        $this->children[] = $event;

        return $event;
    }

    public function getParent(): ?Event
    {
        return $this->parent;
    }

    public function getChildren(): ?array
    {
        return $this->children;
    }

    public function endTermination(): void
    {
        $this->isSelfTerminated = false;
        $this->areOthersTerminated = false;
    }

    public function terminateOthers(): void
    {
        $this->areOthersTerminated = true;
    }

    public function areOthersTerminated(): bool
    {
        return $this->areOthersTerminated;
    }
}
