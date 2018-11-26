<?php

namespace eArc\eventTree\Transformation;

use eArc\EventTree\Api\Interfaces\ObserverTreeFactoryInterface;
use eArc\EventTree\Exceptions\InvalidObserverTreeNameException;
use eArc\EventTree\Api\Interfaces\EventListenerInterface;
use eArc\EventTree\Tree\EventRouter;
use eArc\EventTree\Tree\Observer;
use eArc\EventTree\Tree\ObserverRoot;

class ObserverTreeFactory implements ObserverTreeFactoryInterface
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

    public function get(string $treeName): ObserverRoot
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

    protected function buildTree(string $treeName): Observer
    {
        $tree = new Observer($treeName);

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

    protected function processDir(string $namespace, string $leafName, Observer $leaf): void
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
                /** @var Observer $child */
                $child = $leaf->addChild($fileName);
                $this->processDir(
                    $namespace,
                    $fileName,
                    $child
                );
                chdir('..');
                continue;
            }

            $className = $namespace . '\\' . substr($fileName, 0,-4);

            if (isset($this->ignores[$className]))
            {
                continue;
            }

            if (is_subclass_of($className, EventListenerInterface::class))
            {
                /** @noinspection PhpUndefinedFieldInspection */
                $patience = defined($className . '::EARC_LISTENER_PATIENCE')
                    ? $className::EARC_LISTENER_PATIENCE : 0;

                /** @noinspection PhpUndefinedFieldInspection */
                $type = defined($className . '::EARC_LISTENER_TYPE')
                    ? $className::EARC_LISTENER_TYPE : EventRouter::PHASE_ACCESS;

                /** @noinspection PhpUndefinedFieldInspection */
                $name = defined($className . '::EARC_LISTENER_CONTAINER_ID')
                    ? $className::EARC_LISTENER_CONTAINER_ID : $className;

                $leaf->registerListener($name, $type, $patience);
            }
        }
    }
}
