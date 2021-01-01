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

namespace eArc\EventTree\Util;

use eArc\EventTree\Exceptions\InvalidObserverNodeException;
use eArc\EventTree\Interfaces\ParameterInterface;
use eArc\EventTree\Interfaces\PhaseSpecificListenerInterface;
use eArc\EventTree\Interfaces\SortableListenerInterface;
use eArc\EventTree\Transformation\ObserverTree;
use Iterator;

class CompositeDir implements ParameterInterface
{
    /** @var array */
    protected $listenerCollection = [];
    /** @var array */
    protected $blacklist;

    public function __construct()
    {
        $this->blacklist = di_param(ParameterInterface::BLACKLIST, []);
    }

    public static function getRootsIterator(): Iterator
    {
        foreach (di_param(ParameterInterface::ROOT_DIRECTORIES) as $rootDir => $rootNamespace) {
            chdir(di_param(ParameterInterface::VENDOR_DIR));
            chdir($rootDir);

            yield $rootDir => $rootNamespace;
        }
    }

    /**
     * @param string $realPathRelativeToTreeRoot
     *
     * @return array
     */
    public static function getSubDirNames(string $realPathRelativeToTreeRoot): array
    {
        $redirect = DirectiveReader::getRedirect($realPathRelativeToTreeRoot);

        $dirs = $redirect->validLeafs;

        foreach (static::getRootsIterator() as $rootDir => $rootNamespace) {
            if (!is_dir($realPathRelativeToTreeRoot)) {
                continue;
            }

            chdir($realPathRelativeToTreeRoot);

            foreach (scandir('.', SCANDIR_SORT_NONE) as $name) {
                if ('.' !== $name && '..' !== $name && is_dir($name) && !isset($redirect->invalidLeafs[$name])) {
                    $dirs[$name] = $name;
                }
            }
        }

        sort($dirs);

        return $dirs;
    }

    /**
     * @param string $path
     * @return int[]
     *
     * @throws InvalidObserverNodeException
     */
    public function collectListener(string $path): array
    {
        if (!isset($this->listenerCollection[$path])) {
            $listener = CompositeDir::collectListenerClasses($path);

            asort($listener, SORT_NUMERIC);

            foreach ($listener as $fQCN => $patience) {
                $listener[$fQCN] = self::getPhase($fQCN);
            }

            $this->listenerCollection[$path] = $listener;
        }

        return $this->listenerCollection[$path];
    }

    /**
     * @param string $path
     *
     * @return bool[]
     *
     * @throws InvalidObserverNodeException
     */
    protected function collectListenerClasses(string $path): array
    {
        $listener = [];

        $directoryFound = false;

        foreach(DirectiveReader::getLookup($path) as $realPathRelativeToTreeRoot) {
            $directoryFound = $this->collectListenerClassesByPath($realPathRelativeToTreeRoot, $listener) || $directoryFound;
        }

        if (!$directoryFound && di_param(ParameterInterface::REPORT_INVALID_OBSERVER_NODE, true)) {
            throw new InvalidObserverNodeException(sprintf('Path `%s` is no valid directory for an observer node.', $path));
        }

        return $listener;
    }

    /**
     * @param string $path
     * @param array $listener
     *
     * @return bool
     */
    protected function collectListenerClassesByPath(string $path, array &$listener): bool
    {
        $directoryFound = false;
        $namespace = str_replace('/', '\\', $path);

        foreach (static::getRootsIterator() as $rootDir => $rootNamespace) {
            if (is_dir($path)) {
                chdir($path);
                $directoryFound = true;

                $fullNamespace = $rootNamespace.('.' !== $namespace ? '\\' . $namespace : '');

                foreach (scandir('.', SCANDIR_SORT_NONE) as $fileName) {
                    if (is_file($fileName) && substr($fileName, -4) === '.php') {
                        $fQCN = $fullNamespace.'\\'.substr($fileName, 0, -4);
                        if (!$this->isBlacklisted($fQCN)) {
                            $listener[$fQCN] = self::getPatience($fQCN);
                        }
                    }
                }
            }
        }

        return $directoryFound;
    }

    protected static function getPatience(string $fQCN): float
    {
        /** @var SortableListenerInterface $fQCN */
        return is_subclass_of($fQCN, SortableListenerInterface::class) ? $fQCN::getPatience() : 0;
    }

    protected static function getPhase(string $fQCN): int
    {
        /** @var PhaseSpecificListenerInterface $fQCN */
        return is_subclass_of($fQCN, PhaseSpecificListenerInterface::class) ? $fQCN::getPhase() : ObserverTree::PHASE_ACCESS;
    }

    protected function isBlacklisted(string $fQCN): bool
    {
        return isset($this->blacklist[$fQCN]) && $this->blacklist[$fQCN];
    }
}
