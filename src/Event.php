<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/earc-eventTree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree;

use eArc\EventTree\Transformation\EventFactory;
use eArc\EventTree\Propagation\EventRouter;
use eArc\PayloadContainer\PayloadContainer;
use eArc\Tree\Node;

/**
 * Adds the dispatch method and the factory getters.
 */
class Event extends Node
{
    /** @var PayloadContainer */
    protected $payload;

    /** @var Handler */
    protected $state;

    /** @var Type */
    protected $type;


    /**
     * @param Event|null $parent
     * @param Type|null $type
     * @param bool $inheritPayload
     */
    public function __construct(
        ?Event $parent = null,
        ?Type  $type = null,
        bool $inheritPayload = false
    ) {
        $this->type = $type;
        $this->payload = $inheritPayload ? $parent->getPayload() : new PayloadContainer();

        parent::__construct($parent);

        if ($parent) {
            $this->state = new Handler();
        }
    }

    /**
     * Get the type of the event.
     *
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * Get the handler for the state of the event.
     *
     * @return Handler
     */
    public function getHandler(): Handler
    {
        return $this->state;
    }

    /**
     * Get a specific payload item of the root event.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get(string $name)
    {
        /** @var Event $root */
        $root = $this->getRoot();

        return $root->getPayload()->get($name);
    }

    /**
     * Get the payload from the event.
     *
     * @return PayloadContainer
     */
    public function getPayload(): PayloadContainer
    {
        return $this->payload;
    }

    /**
     * Dispatches the event on its tree according to its type.
     *
     * Hint: Do not call this method on a root event. Root events can not be
     * dispatched.
     *
     * @throws \BadMethodCallException
     */
    public function dispatch(): void
    {
        if ($this === $this->getRoot())
        {
            throw new \BadMethodCallException("A root event can not be dispatched!");
        }

        (new EventRouter($this))->dispatchEvent();
    }

    /**
     * Get the EventFactory for creating an offspring of this event.
     *
     * @return EventFactory
     */
    public function getEventFactory(): EventFactory
    {
        return new EventFactory($this);
    }

    /**
     * Get the EventFactory for creating an offspring of the root event.
     *
     * @return EventFactory
     */
    public function getEventFactoryFromRoot(): EventFactory
    {
        /** @var Event $root */
        $root = $this->getRoot();

        return $root->getEventFactory();
    }
}
