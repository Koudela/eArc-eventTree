<?php

namespace eArc\eventTree\Interfaces;

use eArc\eventTree\Event\Event;

interface EventListener
{
    public function processEvent(Event $event);
}
