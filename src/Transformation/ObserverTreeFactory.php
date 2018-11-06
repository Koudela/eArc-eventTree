<?php

namespace eArc\eventTree\Transformation;

use eArc\eventTree\Exceptions\InvalidObserverTreeNameException;
use eArc\eventTree\Interfaces\EventListener;
use eArc\eventTree\Tree\ObserverLeaf;
use eArc\eventTree\Tree\ObserverTree;

class ObserverTreeFactory
{
    protected $trees = [];
    protected $definitionPointer;
    protected $primaryDirectory;
    protected $ignores;

    public function __construct(
        string $directoryOfObserverTrees,
        string $namespaceOfObserverTrees,
        array $extends = array(),
        array $ignores = array()
    ) {
        $this->primaryDirectory = $directoryOfObserverTrees;
        $this->definitionPointer = $extends;
        $this->definitionPointer[] = [
            $directoryOfObserverTrees,
            $namespaceOfObserverTrees
        ];
        $this->ignores = $ignores;
    }

    public function get(string $treeName): ObserverTree
    {
        if (!isset($this->trees[$treeName]))
        {
            chdir($this->primaryDirectory);

            if (!is_dir($treeName))
            {
                throw new InvalidObserverTreeNameException($treeName);
            }

            $this->trees[$treeName] = $this->buildTree($treeName);
        }

        return $this->trees[$treeName];
    }

    protected function buildTree(string $treeName): ObserverTree
    {
        $tree = new ObserverTree($treeName);

        foreach($this->definitionPointer as list($rootDir, $rootNamespace))
        {
            chdir($rootDir);

            if (is_dir($treeName))
            {
                $this->processDir($rootNamespace, $treeName, $tree);
            }
        }

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

            if (isset($this->ignores[$className]))
            {
                continue;
            }

            if (is_subclass_of($className, EventListener::class))
            {
                /** @noinspection PhpUndefinedFieldInspection */
                $patience = defined($className . '::EARC_LISTENER_PATIENCE')
                    ? $className::EARC_LISTENER_PATIENCE : 0;

                /** @noinspection PhpUndefinedFieldInspection */
                $type = defined($className . '::EARC_LISTENER_TYPE')
                    ? $className::EARC_LISTENER_TYPE : 'access';

                $leaf->registerListener($className, $type, $patience);
            }
        }
    }
}
