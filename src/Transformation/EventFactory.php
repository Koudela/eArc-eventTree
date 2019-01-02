<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/earc-eventTree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Transformation;

use eArc\EventTree\Type;
use eArc\EventTree\Interfaces\EventFactoryInterface;
use eArc\EventTree\Event;
use eArc\ObserverTree\Observer;
use eArc\PayloadContainer\PayloadContainer;

/**
 * The event factory simplifies the creation of an event. Only root events shall
 * be build without the factory.
 */
class EventFactory implements EventFactoryInterface
{
    /** @var Event */
    protected $parent;

    /** @var Observer|null  */
    protected $tree;

    /** @var array|null */
    protected $start;

    /** @var array|null */
    protected $destination;

    /** @var int|null */
    protected $maxDepth = -1;

    /** @var bool */
    protected $inheritPayload = false;

    /** @var array */
    protected $payload = [];

    /** @var string */
    protected $routerClass = null;

    /** @var string */
    protected $factoryClass = null;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->parent = $event;
    }

    /**
     * @inheritdoc
     */
    public function tree(Observer $observerTree): EventFactoryInterface
    {
        $this->tree = $observerTree;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function start(array $start = array()): EventFactoryInterface
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function destination(array $destination = array()): EventFactoryInterface
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function maxDepth(?int $maxDepth = null): EventFactoryInterface
    {
        $this->maxDepth = $maxDepth;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function inheritPayload(bool $inheritPayload = true): EventFactoryInterface
    {
        $this->inheritPayload = $inheritPayload;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addPayload(string $name, $payload, $overwrite = false): EventFactoryInterface
    {
        $this->payload[$name] = [$payload, $overwrite];
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setRouter(string $eventRouterClass): EventFactoryInterface
    {
        $this->routerClass = $eventRouterClass;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setFactory(string $eventFactoryClass): EventFactoryInterface
    {
        $this->factoryClass = $eventFactoryClass;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function build(): Event
    {
        $parentType = $this->parent->expose(Type::class);

        $event = new Event(
            $this->parent,
            new Type(
                $this->tree ?? $parentType->getTree(),
                $this->start ?? $parentType->getStart(),
                $this->destination ?? $parentType->getDestination(),
                $this->maxDepth !== -1 ? $this->maxDepth : $parentType->getMaxDepth()
            ),
            $this->inheritPayload ? null : new PayloadContainer(),
            $this->routerClass,
            $this->factoryClass
        );

        $payloadContainer = $event->expose(PayloadContainer::class);

        foreach ($this->payload as $name => $payload)
        {
            if ($payload[1]) {
                $payloadContainer->overwrite($name, $payload[0]);

                continue;
            }

            $payloadContainer->set($name, $payload[0]);
        }

        return $event;
    }
}
