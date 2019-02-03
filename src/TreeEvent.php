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

namespace eArc\EventTree;

use eArc\EventTree\Exceptions\EventTreeException;
use eArc\EventTree\Exceptions\IsDispatchedException;
use eArc\EventTree\Exceptions\IsNotDispatchedException;
use eArc\EventTree\Exceptions\IsRootEventException;
use eArc\EventTree\Interfaces\RoutingTypeInterface;
use eArc\EventTree\Interfaces\TreeEventFactoryInterface;
use eArc\EventTree\Interfaces\TreeEventInterface;
use eArc\EventTree\Interfaces\TreeEventRouterInterface;
use eArc\EventTree\Interfaces\HandlerInterface;
use eArc\EventTree\Transformation\TreeEventFactory;
use eArc\EventTree\Propagation\TreeEventRouter;
use eArc\PayloadContainer\Traits\PayloadContainerTrait;
use eArc\Tree\Traits\NodeTrait;

/**
 * Adds the dispatch method and the factory getters.
 */
class TreeEvent implements TreeEventInterface
{
    use PayloadContainerTrait;
    use NodeTrait;

    /** @var Handler|null */
    protected $handler;

    /** @var RoutingTypeInterface */
    protected $routingType;

    /** @var string */
    protected $eventRouterClass;

    /** @var string */
    protected $eventFactoryClass;

    /** @var TreeEventRouterInterface|null */
    protected $eventRouter;

    /**
     * @param TreeEvent|null   $parent
     * @param RoutingType|null $routingType
     * @param string|null      $eventRouterClass
     * @param string|null      $eventFactoryClass
     *
     * @throws EventTreeException
     */
    public function __construct(
        ?TreeEvent $parent = null,
        ?RoutingType $routingType = null,
        string $eventRouterClass = null,
        string $eventFactoryClass = null
    ) {
        if (null !== $eventRouterClass
            && !is_subclass_of($eventRouterClass, TreeEventRouterInterface::class)) {
            throw new EventTreeException(sprintf(
                '`%s` need to implement `%s`.',
                $eventRouterClass,
                TreeEventRouterInterface::class
            ));
        }

        if (null !== $eventFactoryClass
            && !is_subclass_of($eventFactoryClass, TreeEventFactoryInterface::class)) {
            throw new EventTreeException(sprintf(
                '`%s` need to implement `%s`.',
                $eventFactoryClass,
                TreeEventFactoryInterface::class
            ));
        }

        $this->routingType = $routingType ?? $parent->routingType;

        $this->eventRouterClass = $eventRouterClass ??
            ($parent ? (function() { return $this->eventRouterClass; })->call($parent) : TreeEventRouter::class);

        $this->eventFactoryClass = $eventFactoryClass ??
            ($parent ? (function() { return $this->eventFactoryClass; })->call($parent) : TreeEventFactory::class);

        $this->initPayloadContainerTrait($parent->getItems());
        $this->initNodeTrait($parent);
    }

    /**
     * @inheritdoc
     */
    public function dispatch(): void
    {
        if ($this === $this->getRoot()) {
            throw new IsRootEventException('A root event can not be dispatched!');
        }

        if ($this->eventRouter instanceof TreeEventRouterInterface) {
            throw new IsDispatchedException('This event has been dispatched already.');
        }

        $this->eventRouter = new $this->eventRouterClass($this, $this->routingType);
        $this->handler = new Handler($this->eventRouter);
        $this->eventRouter->dispatchEvent();
    }

    /**
     * @inheritdoc
     */
    public function fork(): TreeEventFactoryInterface
    {
        return new $this->eventFactoryClass($this);
    }

    /**
     * @inheritdoc
     */
    public function getHandler(): HandlerInterface
    {
        if (null === $this->handler) {
            throw new IsNotDispatchedException('The event has not been dispatched yet.');
        }

        return $this->handler;
    }
}
