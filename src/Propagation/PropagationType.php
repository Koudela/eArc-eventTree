<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * event tree component
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-eventTree/
 * @copyright Copyright (c) 2018-2020 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Propagation;

use eArc\EventTree\EventDispatcher;
use eArc\EventTree\Interfaces\EventDispatcherInterface;
use eArc\EventTree\Interfaces\Propagation\PropagationTypeInterface;

/**
 * Defines an immutable four dimensional event routing type.
 */
class PropagationType implements PropagationTypeInterface
{
    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var string[] */
    protected $start;

    /** @var string[] */
    protected $destination;

    /** @var int|null */
    protected $maxDepth;

    /**
     * @param string[]                 $start
     * @param string[]                 $destination
     * @param int|null                 $maxDepth
     */
    public function __construct(array $start = [], array $destination = [], ?int $maxDepth = 0)
    {
        $this->start = $start;
        $this->destination = $destination;
        $this->maxDepth = $maxDepth;
        $this->initDispatcher();
    }

    public function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    public function getStart(): array
    {
        return $this->start;
    }

    public function getDestination(): array
    {
        return $this->destination;
    }

    public function getMaxDepth(): ?int
    {
        return $this->maxDepth;
    }

    public function __sleep(): array
    {
        return ['start', 'destination', 'maxDepth'];
    }

    public function __wakeup(): void
    {
        $this->initDispatcher();
    }

    protected function initDispatcher()
    {
        $this->dispatcher = di_is_decorated(EventDispatcherInterface::class)
            ? di_get(EventDispatcherInterface::class)
            : di_get(EventDispatcher::class);
    }
}
