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

use eArc\EventTree\Exceptions\NodeOverwriteException;

/**
 * Node defines the tree structure of the composite
 */
class Node
{
    /** @var Node */
    protected $root;

    /** @var Node */
    protected $parent;

    /** @var array */
    protected $children = [];

    /** @var string */
    protected $name;

    /**
     * @param Node|null $parent
     * @param null|string $name
     */
    public function __construct(?Node $parent = null, ?string $name = null)
    {
        if (!$parent) {
            $this->root = $this;
            $this->parent = $this;
        } else {
            $this->root = $parent->getRoot();
            $this->parent = $parent;
        }
        $this->name = $name ?? spl_object_hash($this);
    }

    /**
     * Get the name of the node.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the parent of the node or the node itself if it's a root node.
     *
     * @return Node
     */
    public function getParent(): Node
    {
        return $this->parent;
    }

    /**
     * Get the children of the node.
     *
     * @return array
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Add a named children to the node.
     *
     * @param string $name
     *
     * @return Node
     */
    public function addChild(string $name = null): Node
    {
        if (null !== $name && isset($this->children[$name])) {
            throw new NodeOverwriteException(
                "A child with the name '$name' already exists.'"
            );
        }

        /** @var Node $child */
        $child = new (get_class())($this, $name);

        $this->children[$child->getName()] = $child;

        return $child;
    }

    /**
     * Get a child by its name.
     *
     * @param string $name
     *
     * @return Node
     */
    public function getChild(string $name): Node
    {
        return $this->children[$name];
    }

    /**
     * Get the root of the node.
     *
     * @return Node
     */
    public function getRoot(): Node
    {
        return $this->root;
    }

    /**
     * Transforms the composite into a string representation.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->root->nodeToString();
    }

    /**
     * Transforms the instance and its children into a string representation.
     *
     * @param string $indent
     *
     * @return string
     */
    protected function nodeToString($indent = ''): string
    {
        $str = $indent . "--{$this->name}--\n";

        foreach ($this->children as $child)
        {
            $str .= $child->nodeToString($indent . '  ');
        }

        return $str;
    }
}
