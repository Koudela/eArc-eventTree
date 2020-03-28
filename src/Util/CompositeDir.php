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
     * @param string $realPathRelativeToTreeRoot
     *
     * @return array
     */
    public static function getSubDirNames(string $realPathRelativeToTreeRoot): array
    {
        $dirs = [];

        $invalid = static::getRedirectSubDirNames($realPathRelativeToTreeRoot, $dirs);

        foreach (di_param(ParameterInterface::ROOT_DIRECTORIES) as $rootDir => $rootNamespace) {
            chdir(di_param(ParameterInterface::VENDOR_DIR));
            chdir($rootDir);

            if (!is_dir($realPathRelativeToTreeRoot)) {
                continue;
            }

            chdir($realPathRelativeToTreeRoot);

            foreach (scandir('.', SCANDIR_SORT_NONE) as $name) {
                if ('.' !== $name && '..' !== $name && is_dir($name) && !isset($invalid[$name])) {
                    $dirs[$name] = $name;
                }
            }
        }

        sort($dirs);

        return $dirs;
    }

    protected static function getRedirectSubDirNames(string $realPathRelativeToTreeRoot, array &$names): array
    {
        $invalid = [];

        foreach (static::getRedirect($realPathRelativeToTreeRoot) as $key => $value) {
            if ('' !== $value) {
                $names[$key] = $key;
            } else {
                $invalid[$key] = $key;
            }
        }

        return $invalid;
    }

    /**
     * @param string $realPathRelativeToTreeRoot
     * @param string $name
     *
     * @return string
     *
     * @throws InvalidObserverNodeException
     */
    public static function getNextPath(string $realPathRelativeToTreeRoot, string $name): string
    {
        $redirect = static::getRedirect($realPathRelativeToTreeRoot);

        $short = $realPathRelativeToTreeRoot === '.';

            if (!isset($redirect[$name]) || '~' === $redirect[$name]) {
            return $short ? $name : $realPathRelativeToTreeRoot.'/'.$name;
        }

        if ('~/' === substr($redirect[$name], 0, 2)) {
            $name = substr($redirect[$name], 2);

            return $short ? $name : $realPathRelativeToTreeRoot.'/'.$name;
        }

        if ('' === $redirect[$name]) {
            throw new InvalidObserverNodeException(sprintf('Path `%s` is no valid observer node extending `%s`.', $name, $realPathRelativeToTreeRoot));
        }

        return $redirect[$name];
    }

    /**
     * @param string $realPathRelativeToTreeRoot
     * @return string[]
     */
    protected static function getRedirect(string $realPathRelativeToTreeRoot): array
    {
        $redirect = [];

        foreach (di_param(ParameterInterface::ROOT_DIRECTORIES) as $rootDir => $rootNamespace) {
            chdir(di_param(ParameterInterface::VENDOR_DIR));
            chdir($rootDir);

            if (!is_dir($realPathRelativeToTreeRoot)) {
                continue;
            }

            chdir($realPathRelativeToTreeRoot);

            if (is_file('.redirect')) {
                self::readRedirect(file_get_contents('.redirect'), $redirect);
            }
        }

        return $redirect;
    }

    /**
     * @param string   $fileContent
     * @param string[] $result
     */
    protected static function readRedirect(string $fileContent, array &$result): void
    {
        foreach (explode("\n", $fileContent) as $line) {
            $row = explode(' ', $line, 3);
            if ('' !== $row[0]) {
                $result[$row[0]] = isset($row[1]) ? $row[1] : '';
            }
        }
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
        $namespace = str_replace('/', '\\', $path);
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
                var_dump("$rootNamespace --- $path --- $namespace");

                static::processDir($rootNamespace.('.' !== $namespace ? '\\'.$namespace : ''), $listener);
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
var_dump($namespace.'\\'.$className);
            $listener[$namespace.'\\'.$className] = $namespace.'\\'.$className;
        }
    }
}
