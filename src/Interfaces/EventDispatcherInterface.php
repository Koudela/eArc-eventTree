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

namespace eArc\eventTree\Interfaces;

use Psr\EventDispatcher\EventDispatcherInterface as BaseInterface;

interface EventDispatcherInterface extends BaseInterface
{
    /**
     * @param TreeEventInterface $event
     *
     * @return TreeEventInterface
     */
    public function dispatch($event): TreeEventInterface;
}
