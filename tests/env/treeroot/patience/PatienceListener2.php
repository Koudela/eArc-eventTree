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

namespace eArc\EventTreeTests\env\treeroot\patience;

use eArc\EventTree\Interfaces\SortableListenerInterface;
use eArc\EventTreeTests\env\BaseListener;

class PatienceListener2 extends BaseListener implements SortableListenerInterface
{
    public static function getPatience(): float
    {
        return -2.8;
    }
}
