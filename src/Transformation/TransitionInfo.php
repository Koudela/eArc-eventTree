<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * event tree component
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-eventTree/
 * @copyright Copyright (c) 2018-2019 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\eventTree\Transformation;

use eArc\eventTree\Interfaces\Transformation\TransitionInfoInterface;

class TransitionInfo implements TransitionInfoInterface
{
    /** @var string[] */
    protected $currentPath = [];

    /** @var array */
    protected $treeVisited = [];

    public function getCurrentPath(): array
    {
        return $this->currentPath;
    }

    public function getCurrentPathFormatted(string $delimiter = ','): string
    {
        return implode($delimiter, $this->currentPath);
    }

    public function getTreeVisited(): array
    {
        return $this->treeVisited;
    }

    public function addChild(string $name): void
    {
        $tree =& $this->treeVisited;

        foreach ($this->currentPath as $node) {
            $tree =& $tree[$node];
        }

        $tree[$name] = [];

        $this->currentPath[] = $name;
    }

    public function goToParent(): void
    {
        array_pop($this->currentPath);
    }
}
