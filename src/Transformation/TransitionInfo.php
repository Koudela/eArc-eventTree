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

namespace eArc\EventTree\Transformation;

use eArc\EventTree\Interfaces\Transformation\TransitionInfoInterface;
use function array_key_last;

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

    public function getCurrentRealPath(): string
    {
        return array_key_last($this->currentPath) ?? '.';
    }

    public function getTreeVisited(): array
    {
        return $this->treeVisited;
    }

    public function addChild(string $name, string $path): void
    {
        $tree =& $this->treeVisited;

        foreach ($this->currentPath as $node) {
            $tree =& $tree[$node];
        }

        $tree[$name] = [];

        $this->currentPath[$path] = $name;
    }

    public function goToParent(): void
    {
        array_pop($this->currentPath);
    }
}
