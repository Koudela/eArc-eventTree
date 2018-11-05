<?php

namespace eArc\eventTree\Tree;

class ObserverTree extends ObserverLeaf
{
    protected $identifier;

    public function __construct(string $identifier)
    {
        $this->root = $this;
        parent::__construct($this);
        $this->identifier = $identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function toString($indent = ''): string
    {
        return
            $indent . "--{$this->getIdentifier()}--\n" .
            parent::toString($indent . '  ');
    }
}