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

use eArc\EventTree\Exceptions\EventException;
use eArc\EventTree\Interfaces\EventFactoryInterface;
use eArc\EventTree\Interfaces\EventRouterInterface;
use eArc\EventTree\Transformation\EventFactory;
use eArc\EventTree\Propagation\EventRouter;
use eArc\PayloadContainer\Exceptions\ItemNotFoundException;
use eArc\PayloadContainer\PayloadContainer;
use eArc\Tree\ContentNode;

/**
 * Adds the dispatch method and the factory getters.
 */
class Event
{
    /** @var ContentNode */
    protected $lineage;

    /** @var PayloadContainer */
    protected $payload;

    /** @var Handler|null */
    protected $handler;

    /** @var Type */
    protected $type;

    /** @var string */
    protected $eventRouterClass;

    /** @var string */
    protected $eventFactoryClass;

    /** @var EventRouterInterface|null */
    protected $eventRouter;

    /**
     * @param Event|null $parent
     * @param Type|null $type
     * @param PayloadContainer|null $payload
     * @param string|null $eventRouterClass
     * @param string|null $eventFactoryClass
     */
    public function __construct(
        ?Event $parent = null,
        ?Type  $type = null,
        ?PayloadContainer $payload = null,
        string $eventRouterClass = null,
        string $eventFactoryClass = null
    ) {
        $this->lineage = new ContentNode(
            ($parent ? $parent->expose(ContentNode::class) : null),
            null,
            $this
        );

        $this->type = $type ??
            ($parent ? $parent->expose(Type::class) : null);

        $this->payload = $payload ??
            ($parent ? $parent->expose(PayloadContainer::class) : new PayloadContainer());

        $this->eventRouterClass = $eventRouterClass ??
            ($parent ? $parent->expose(EventRouterInterface::class) : EventRouter::class);

        $this->eventFactoryClass = $eventFactoryClass ??
            ($parent ? $parent->expose(EventFactoryInterface::class) : EventFactory::class);
    }

    /**
     * Checks whether a specific payload item exists (locally or at root).
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name)
    {
        return ($this->payload->has($name)
            || $this->getRoot()->expose(PayloadContainer::class)->has($name));
    }

    /**
     * Get a specific payload item (locally or from root).
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws ItemNotFoundException
     */
    public function get(string $name)
    {
        if ($this->payload->has($name)) {

            return $this->payload->get($name);
        }

        return $this->getRoot()
            ->expose(PayloadContainer::class)
            ->get($name);
    }

    /**
     * Calls a specific closure item (locally or from root).
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function call(string $name, array $arguments)
    {
        if ($this->payload->has($name)) {

            return $this->payload->call($name, $arguments);
        }

        return $this->getRoot()
            ->expose(PayloadContainer::class)
            ->call($name, $arguments);
    }

    /**
     * Sets a specific item locally.
     *
     * @param string $name
     * @param $item
     */
    public function set(string $name, $item): void
    {
        $this->payload->set($name, $item);
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
        if ($this->eventRouter instanceof EventRouterInterface) {
            throw new EventException('This event has been dispatched already.');
        }

        if ($this === $this->getRoot()) {
            throw new EventException('A root event can not be dispatched!');
        }

        $this->eventRouter = new $this->eventRouterClass($this);

        if (!$this->eventRouter instanceof EventRouterInterface) {
            throw new EventException(
                '`'.$this->eventRouterClass.'` need to implement the EventRouterInterface.'
            );
        }

        $this->handler = new Handler($this->eventRouter);
        $this->eventRouter->dispatchEvent();
    }


    /**
     * Get the EventFactory for creating an offspring of this event.
     *
     * @return EventFactory
     */
    public function getEventFactory(): EventFactoryInterface
    {
        return new $this->eventFactoryClass($this);
    }

    /**
     * Get root of event.
     */
    public function getRoot(): Event
    {
        /** @var ContentNode $root */
        $root = $this->lineage->getRoot();

        return $root->getContent();
    }

    /**
     * Get the handler for the state of the event.
     *
     * @return Handler
     */
    public function getHandler(): Handler
    {
        if (null === $this->handler) {
            throw new EventException('The event has not been dispatched yet.');
        }

        return $this->handler;
    }

    /**
     * Get property from Event.
     *
     * @param string $type
     *
     * @return PayloadContainer|Type|ContentNode|string|null
     */
    public function expose(string $type)
    {
        switch ($type) {
            case ContentNode::class:
                return $this->lineage;
            case Type::class:
                return $this->type;
            case EventRouterInterface::class:
                return $this->eventRouterClass;
            case EventFactoryInterface::class:
                return $this->eventFactoryClass;
            case PayloadContainer::class:
            default:
                 return $this->payload;
        }
    }
}
