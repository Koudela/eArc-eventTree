<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/earc-eventTree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Tree;

/**
 * This is the root instance of the composite.
 */
class ObserverRoot extends Observer
{
    public function __construct(string $name)
    {
        parent::__construct(null, $name);
    }
}
