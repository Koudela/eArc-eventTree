<?php

namespace eArc\eventTree\Tree;

use eArc\eventTree\Interfaces\EventObserver;
use eArc\eventTree\Traits\Listenable;
use eArc\eventTree\Traits\TreeHeritable;
use eArc\eventTree\Interfaces\TreeInheritanceHandler;

class ObserverLeaf implements EventObserver, TreeInheritanceHandler
{
    use TreeHeritable;
    use Listenable;

    public function __construct(ObserverLeaf $parent)
    {
        $this->parent = $parent;
        $this->root = $parent->getRoot();
    }

    public function addChild(string $name): ObserverLeaf
    {
        $leaf = new ObserverLeaf($this);

        $this->children[$name] = $leaf;

        return $leaf;
    }

    public function toString($indent = '  '): string
    {
        $str = '';

        foreach ($this->listener as $FQN => $patience)
        {
            $str .= $indent . '  ' . $FQN . ': '
                . "{ patience: $patience, type: "
                . $this->eventPhasesToString($this->eventPhases[$FQN]) . " }\n";
        }

        return $str . $this->childrenToString($indent);
    }

    protected function childrenToString($indent = '  '): string
    {
        $str = '';
        $indent .= '  ';

        foreach ($this->getChildren() as $name => $leaf)
        {
            $str .= $indent . "--$name--\n";
            $str .= $this->getChild($name)->toString($indent . '  ');
        }

        return $str;
    }

    protected function eventPhasesToString(int $eventPhases): string
    {
        if (EventRouter::PHASE_ACCESS === $eventPhases) {
            return 'access';
        }

        $arr = [];

        if (EventRouter::PHASE_START & $eventPhases) {
            $arr[] = 'start';
        }

        if (EventRouter::PHASE_BEFORE & $eventPhases) {
            $arr[] = 'before';
        }

        if (EventRouter::PHASE_DESTINATION & $eventPhases) {
            $arr[] = 'destination';
        }

        if (EventRouter::PHASE_BEYOND & $eventPhases) {
            $arr[] = 'beyond';
        }

        return implode(' | ', $arr);
    }
}
