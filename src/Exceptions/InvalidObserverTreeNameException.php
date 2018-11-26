<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/earc-eventTree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Exceptions;

use Throwable;

/**
 * Gets thrown if a name does not belong to any ObserverTree.
 */
class InvalidObserverTreeNameException extends \InvalidArgumentException
{
    public function __construct(string $treeName = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct("Name `$treeName` does not point to an ObserverTree", $code, $previous);
    }
}
