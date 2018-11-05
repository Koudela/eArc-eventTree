<?php

namespace eArc\eventTree\Transformation;

use eArc\eventTree\Interfaces\EventListener;
use eArc\eventTree\Tree\ObserverLeaf;
use eArc\eventTree\Tree\ObserverTree;

class ObserverTreeFactory
{
    protected $rootDir;
    protected $rootNamespace;
    protected $trees = [];

    public function __construct(string $directoryOfObserverTrees, string $namespaceOfObserverTrees)
    {
        $this->rootDir = $directoryOfObserverTrees;
        $this->rootNamespace = $namespaceOfObserverTrees;
    }

    public function get(string $treeName): ObserverTree
    {
        if (!isset($this->trees[$treeName]))
        {
            $this->trees[$treeName] = $this->initDir($treeName);
        }

        return $this->trees[$treeName];
    }

    protected function initDir(string $treeName): ObserverTree
    {
        chdir($this->rootDir);
        $tree = new ObserverTree($treeName);

        $this->processDir($this->rootNamespace, $treeName, $tree);

        return $tree;
    }

    protected function processDir(string $namespace, string $leafName, ObserverLeaf $leaf): void
    {
        chdir($leafName);
        $namespace .= '\\' . $leafName;

        foreach (scandir('.', SCANDIR_SORT_NONE) as $fileName)
        {
            if ('.' === $fileName || '..' === $fileName) {
                continue;
            }

            if (is_dir($fileName))
            {
                $this->processDir(
                    $namespace,
                    $fileName,
                    $leaf->addChild($fileName)
                );
                chdir('..');
                continue;
            }

            $className = $namespace . '\\' . substr($fileName, 0,-4);

            if (is_subclass_of($className, EventListener::class))
            {
                $patience = defined($className::EARC_LISTENER_PATIENCE)
                    ? $className::EARC_LISTENER_PATIENCE : 0;

                $type = defined($className::EARC_LISTENER_TYPE)
                    ? $className::EARC_LISTENER_TYPE : 'access';

                $leaf->registerListener($className, $type, $patience);
            }
        }
    }
}
