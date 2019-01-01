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

use eArc\EventTree\Interfaces\EventFactoryInterface;
use eArc\EventTree\Interfaces\EventRouterInterface;
use eArc\EventTree\Transformation\EventFactory;
use eArc\EventTree\Propagation\EventRouter;
use eArc\PayloadContainer\Exceptions\ItemNotFoundException;
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

    /** @var string */
    protected $eventRouter;

    /** @var string */
    protected $eventFactory;

    /**
     * @param Event|null $parent
     * @param Type|null $type
     * @param bool $inheritPayload
     * @param string|null $eventRouterClass
     * @param string|null $eventFactoryClass
     */
    public function __construct(
        ?Event $parent = null,
        ?Type  $type = null,
        bool $inheritPayload = false,
        string $eventRouterClass = null,
        string $eventFactoryClass = null
    ) {
        $this->type = $type;
        $this->payload = $inheritPayload && $parent
            ? $parent->getPayload() : new PayloadContainer();

        parent::__construct($parent);

        if ($parent) {
            if (!$eventRouterClass) {
                $eventRouterClass = $parent->getEventRouterClass();
            }
            if (!$eventFactoryClass) {
                $eventFactoryClass = $parent->getEventFactoryClass();
            }
            $this->state = new Handler();
        }

        $this->eventRouter = $eventRouterClass ?? EventRouter::class;
        $this->eventFactory = $eventFactoryClass ?? EventFactory::class;
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
     * Checks whether a specific payload item of the root event exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name)
    {
        /** @var Event $root */
        $root = $this->getRoot();

        return $root->getPayload()->has($name);
    }

    /**
     * Get a specific payload item of the root event.
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws ItemNotFoundException
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
        if ($this === $this->getRoot()) {
            throw new \BadMethodCallException("A root event can not be dispatched!");
        }

        $eventRouter = new $this->eventRouter($this);

        if (!$eventRouter instanceof EventRouterInterface) {
            throw new \RuntimeException('`'.$this->eventRouter.'` implements not the EventRouterInterface.');
        }

        $eventRouter->dispatchEvent();
    }

    /**
     * Get the EventFactory for creating an offspring of this event.
     *
     * @return EventFactory
     */
    public function getEventFactory(): EventFactoryInterface
    {
        return new $this->eventFactory($this);
    }

    /**
     * Get the EventFactory for creating an offspring of the root event.
     *
     * @return EventFactory
     */
    public function getEventFactoryFromRoot(): EventFactoryInterface
    {
        /** @var Event $root */
        $root = $this->getRoot();

        return $root->getEventFactory();
    }

    /**
     * Get the referenced event router class.
     *
     * @return string
     */
    public function getEventRouterClass(): string
    {
        return $this->eventRouter;
    }

    /**
     * Get the referenced event factory class.
     *
     * @return string
     */
    public function getEventFactoryClass(): string
    {
        return $this->eventFactory;
    }

    /**
     * Checks the payload (first the own, then the one of the root) if it has a
     * closure named like the method call.
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function call($name, $arguments)
    {
        if ($this->payload->has($name)) {
            $item = $this->payload->get($name);
            if ($item instanceof \Closure) {

                return $item(...$arguments);
            }
        }

        /** @var Event $root */
        $root = $this->getRoot();
        if ($root->getPayload()->has($name)) {
            $item = $root->getPayload()->get($name);
            if ($item instanceof \Closure) {
                return $item(...$arguments);
            }
        }

        throw new \BadMethodCallException();
    }
}
