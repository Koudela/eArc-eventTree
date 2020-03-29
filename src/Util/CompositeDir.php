<?php declare(strict_types=1);
/**
 * e-Arc Framework - the explicit Architecture Framework
 * event tree component
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-eventTree/
 * @copyright Copyright (c) 2018-2020 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\EventTree\Util;

use eArc\EventTree\Exceptions\InvalidObserverNodeException;
use eArc\EventTree\Interfaces\ParameterInterface;
use Iterator;

class CompositeDir implements ParameterInterface
{
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
     *
     * @return string[]
     *
     * @throws InvalidObserverNodeException
     */
    public static function collectListener(string $path): array
    {
        $listener = [];

        $directoryFound = false;

        foreach(DirectiveReader::getLookup($path) as $realPathRelativeToTreeRoot) {
            $directoryFound = static::collectLocalListener($realPathRelativeToTreeRoot, $listener) || $directoryFound;
        }

        if (!$directoryFound) {
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
    protected static function collectLocalListener(string $path, array &$listener): bool
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
                        $className = $fullNamespace.'\\'.substr($fileName, 0, -4);
                        $listener[$className] = $className;
                    }
                }
            }
        }

        return $directoryFound;
    }
}
