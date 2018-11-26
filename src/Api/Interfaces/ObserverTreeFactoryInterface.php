<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/earc-eventTree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Api\Interfaces;

use eArc\EventTree\Tree\ObserverRoot;

/**
 * Factory for building and handling observer trees with a common root.
 */
interface ObserverTreeFactoryInterface
{
    /**
     * Get the ObserverRoot instance of a composite associated with the
     * $treeName identifier. Each composite is instantiated only once.
     *
     * @param string $treeName
     *
     * @return ObserverRoot
     */
    public function get(string $treeName): ObserverRoot;
}