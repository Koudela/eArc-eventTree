<?php

namespace eArc\eventTree\Tree;

use eArc\eventTree\Interfaces\EventObserver;
use eArc\eventTree\traits\Listenable;
use eArc\eventTree\traits\TreeHeritable;
use Interfaces\TreeInheritanceHandler;

class EventLeaf implements EventObserver, TreeInheritanceHandler
{
    use TreeHeritable;
    use Listenable;

    public function __construct(EventLeaf $parent)
    {
        $this->parent = $parent;
        $this->root = $parent->getRoot();
    }

    public function addChild(string $name): EventLeaf
    {
        $leaf = new EventLeaf($this);

        $this->children[$name] = $leaf;

        return $leaf;
    }

    public function toString($indent = '  '): string
    {
        $str = $indent . "listener:\n";
        $indent .= '  ';

        foreach ($this->listener as $FQN => $patience)
        {
            $str .= $indent . $FQN . ":\n";
            $str .= $indent . '  patience: ' . $patience . "\n";
            $str .= $indent . '  type: ' . $this->type[$FQN] . "\n";
            $str .= $this->childrenToString($indent . '  ');
        }
    }

    protected function childrenToString($indent = '  '): string
    {
        $str = $indent . "children:\n";
        $indent .= '  ';

        foreach ($this->getChildren() as $name => $leaf)
        {
            $str .= $indent . $name . ":\n";
            $str .= $this->getChild($name)->toString($indent . '  ');
            $str .= $this->getChild($name)->childrenToString($indent . '  ');
        }

        return $str;
    }
}
