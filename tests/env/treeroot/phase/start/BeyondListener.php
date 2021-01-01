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

namespace eArc\EventTreeTests\env\treeroot\phase\start;

use eArc\EventTree\Interfaces\PhaseSpecificListenerInterface;
use eArc\EventTree\Interfaces\Transformation\ObserverTreeInterface;
use eArc\EventTreeTests\env\BaseListener;

class BeyondListener  extends BaseListener implements PhaseSpecificListenerInterface
{
    public static function getPhase(): int
    {
        return ObserverTreeInterface::PHASE_BEYOND;
    }
}
