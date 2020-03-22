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

namespace eArc\EventTreeTests\env;

use eArc\Observer\Interfaces\EventInterface;
use eArc\Observer\Interfaces\ListenerInterface;

/**
 * Class BaseListener.
 */
class BaseListener implements ListenerInterface
{
    /**
     * @inheritDoc
     */
    public function process(EventInterface $event): void
    {
        if ($event instanceof TestEvent) {
            $event->isTouchedByListener[get_class($this)] = array_slice(explode('\\', get_class($this)), -2, 1)[0];
        }
    }
}