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

namespace eArc\EventTreeTests\env\treeroot\leaf1\leaf12\leaf121;

use eArc\EventTreeTests\BaseListener;
use eArc\EventTreeTests\TestEvent;
use eArc\Observer\Interfaces\EventInterface;

class BasicListener extends BaseListener
{
    /**
     * @inheritDoc
     */
    public function process(EventInterface $event): void
    {
        parent::process($event);

        if ($event instanceof TestEvent && $event->testHandlerAssertions) {
            $event->getHandler()->kill();
        }
    }
}