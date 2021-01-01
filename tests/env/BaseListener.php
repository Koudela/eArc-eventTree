<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * event tree component
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-eventTree/
 * @copyright Copyright (c) 2018-2021 Thomas Koudela
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
    static $i = 0;

    /**
     * @inheritDoc
     */
    public function process(EventInterface $event): void
    {


        if ($event instanceof TestEvent) {
            $arr = $event->getTransitionInfo()->getCurrentPath();
            $event->isTouchedByListener[(self::$i++).'_'.get_class($this)] = array_pop($arr);
        }
    }
}
