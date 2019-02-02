<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 * event tree component
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-eventTree/
 * @copyright Copyright (c) 2018-2019 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace Exceptions;

use eArc\EventTree\Exceptions\EventTreeException;

/**
 * Some things cannot be done with root events.
 */
class IsRootEventException extends EventTreeException
{
}
