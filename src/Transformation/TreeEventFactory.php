<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * event tree component
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-eventTree/
 * @copyright Copyright (c) 2018-2019 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Transformation;

use eArc\EventTree\Interfaces\RoutingTypeInterface;
use eArc\EventTree\Interfaces\TreeEventFactoryInterface;
use eArc\EventTree\Interfaces\TreeEventInterface;
use eArc\ObserverTree\Interfaces\ObserverTreeInterface;

/**
 * The event factory simplifies the creation of an event. Only root events shall
 * be build without the factory.
 */
class TreeEventFactory implements TreeEventFactoryInterface
{
    /** @var TreeEventInterface */
    protected $parent;

    /** @var RoutingTypeInterface  */
    protected $routingType;

    /** @var ObserverTreeInterface|null  */
    protected $observerTree;

    /** @var string[]|null */
    protected $start;

    /** @var string[]|null */
    protected $destination;

    /** @var int|null */
    protected $maxDepth = -1;

    /** @var bool */
    protected $inheritPayload = true;

    /** @var array */
    protected $payload = [];

    /** @var string */
    protected $routerClass = null;

    /** @var string */
    protected $factoryClass = null;

    /**
     * @param TreeEventInterface $event
     * @param RoutingTypeInterface $routingType
     */
    public function __construct(TreeEventInterface $event, RoutingTypeInterface $routingType)
    {
        $this->parent = $event;
        $this->routingType = $routingType;
    }

    /**
     * @inheritdoc
     */
    public function tree(ObserverTreeInterface $observerTree): TreeEventFactoryInterface
    {
        $this->observerTree = $observerTree;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function start(array $start = array()): TreeEventFactoryInterface
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function destination(array $destination = array()): TreeEventFactoryInterface
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function maxDepth(?int $maxDepth = null): TreeEventFactoryInterface
    {
        $this->maxDepth = $maxDepth;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function inheritPayload(bool $inheritPayload = true): TreeEventFactoryInterface
    {
        $this->inheritPayload = $inheritPayload;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addPayload(string $name, $payload, $overwrite = false): TreeEventFactoryInterface
    {
        $this->payload[$name] = [$payload, $overwrite];
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setRouter(string $eventRouterClass): TreeEventFactoryInterface
    {
        $this->routerClass = $eventRouterClass;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setFactory(string $eventFactoryClass): TreeEventFactoryInterface
    {
        $this->factoryClass = $eventFactoryClass;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function build(): TreeEventInterface
    {
        $eventTreeClass = get_class($this->parent);
        $routingTypeClass = get_class($this->routingType);
        /** @var TreeEventInterface $event */
        $event = new $eventTreeClass(
            $this->parent,
            new $routingTypeClass(
                $this->observerTree ?? $this->routingType->getTree(),
                $this->start ?? $this->routingType->getStart(),
                $this->destination ?? $this->routingType->getDestination(),
                $this->maxDepth !== -1 ? $this->maxDepth : $this->routingType->getMaxDepth()
            ),
            $this->routerClass,
            $this->factoryClass
        );

        if (!$this->inheritPayload) {
            $event->resetItems();
        }

        foreach ($this->payload as $name => $payload)
        {
            if ($payload[1]) {
                $event->overwrite($name, $payload[0]);

                continue;
            }

            $event->set($name, $payload[0]);
        }

        return $event;
    }
}
