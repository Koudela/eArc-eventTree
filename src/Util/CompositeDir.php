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

class CompositeDir implements ParameterInterface
{
    /**
     * @param string $path
     *
     * @return array
     */
    public static function getSubDirNames(string $path): array
    {
        $dirs = [];

        foreach (di_param(ParameterInterface::ROOT_DIRECTORIES) as $rootDir => $rootNamespace) {
            chdir(di_param(ParameterInterface::VENDOR_DIR));
            chdir($rootDir);

            if (!is_dir($path)) {
                return $dirs;
            }

            chdir($path);

            foreach (scandir('.', SCANDIR_SORT_NONE) as $fileName) {
                if ('.' !== $fileName && '..' !== $fileName && is_dir($fileName)) {
                    $dirs[] = $fileName;
                }
            }
        }

        sort($dirs);

        return $dirs;
    }

    /**
     * @param string $path
     * @param string $namespace
     *
     * @return string[]
     *
     * @throws InvalidObserverNodeException
     */
    public static function collectListener(string $path, string $namespace): array
    {
        $listener = null;

        foreach (di_param(ParameterInterface::ROOT_DIRECTORIES) as $rootDir => $rootNamespace)
        {
            chdir(di_param(ParameterInterface::VENDOR_DIR));
            chdir($rootDir);

            if (is_dir($path))
            {
                chdir($path);

                if (null === $listener) {
                    $listener = [];
                }

                static::processDir($rootNamespace.($namespace ? '\\'.$namespace : ''), $listener);
            }
        }

        if (null === $listener) {
            throw new InvalidObserverNodeException(sprintf('Path `%s` is no valid directory for an observer node.', $path));
        }

        return $listener;
    }

    /**
     * @param string   $namespace
     * @param string[] $listener
     */
    protected static function processDir(string $namespace, array &$listener): void
    {
        foreach (scandir('.', SCANDIR_SORT_NONE) as $fileName)
        {
            if ('.' === $fileName || '..' === $fileName || is_dir($fileName) || substr($fileName, -4) !== '.php') {
                continue;
            }

            $className = substr($fileName, 0,-4);

            $listener[$className] = $namespace.'\\'.$className;
        }
    }
}
