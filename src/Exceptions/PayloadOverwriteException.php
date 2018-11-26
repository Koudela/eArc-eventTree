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

/**
 * Gets thrown if a payload slot of the event is overwritten unintentionally.
 */
class PayloadOverwriteException extends \RuntimeException
{
}
