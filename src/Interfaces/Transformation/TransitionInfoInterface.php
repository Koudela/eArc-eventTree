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

namespace eArc\EventTree\Interfaces\Transformation;

interface TransitionInfoInterface
{
    /**
     * Get an array representation of the current path, where the values are the
     * node names.
     *
     * @return string[]
     */
    public function getCurrentPath(): array;

    /**
     * Get the current path in a string representation.
     *
     * @param string $delimiter
     *
     * @return string
     */
    public function getCurrentPathFormatted(string $delimiter = ','): string;

    /**
     * Get an array representation of the visited nodes, where the keys are the
     * node names.
     *
     * @return array
     */
    public function getTreeVisited(): array;

    /**
     * Add a child to current node and set the pointer to the child node.
     *
     * @param string $name
     */
    public function addChild(string $name): void;

    /**
     * Set pointer to the parent of the current node.
     */
    public function goToParent(): void;
}
