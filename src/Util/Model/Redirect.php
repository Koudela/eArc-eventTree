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

namespace eArc\EventTree\Util\Model;

use eArc\EventTree\Exceptions\InvalidObserverNodeException;
use eArc\EventTree\Util\CompositeDir;

class Redirect
{
    public $realPathRelativeToTreeRoot;
    public $invalidLeafs = [];
    public $validLeafs = [];
    public $leafPaths = [];

    public function __construct(string $realPathRelativeToTreeRoot)
    {
        $this->realPathRelativeToTreeRoot = '' !== $realPathRelativeToTreeRoot ? $realPathRelativeToTreeRoot : '.';
        $this->extract();
    }

    /**
     * @param string $leafName
     *
     * @return string
     *
     * @throws InvalidObserverNodeException
     */
    public function getPathForLeaf(string $leafName): string
    {
        if (isset($this->invalidLeafs[$leafName])) {
            throw new InvalidObserverNodeException(sprintf('{fa25945b-2744-4e65-baa7-ee3c99d2c9a7} Leaf `%s` is excluded by the .redirect directive of `%s`.', $leafName, $this->realPathRelativeToTreeRoot));
        }

        if (!isset($this->leafPaths[$leafName]) || '~' === $this->leafPaths[$leafName]) {
            return $this->getCanonicalPath($leafName);
        }

        if ('~/' === substr($this->leafPaths[$leafName], 0, 2)) {
            return $this->getCanonicalPath(substr($this->leafPaths[$leafName], 2));
        }

        return $this->leafPaths[$leafName];
    }

    protected function getCanonicalPath(string $leafName): string
    {
        return '.' !== $this->realPathRelativeToTreeRoot ? $this->realPathRelativeToTreeRoot.'/'.$leafName : $leafName;
    }

    protected function extract()
    {
        foreach ($this->readDirectives() as $leafName => $rawPath) {
            if ('' === $rawPath) {
                $this->invalidLeafs[$leafName] = true;

                continue;
            }

            $this->leafPaths[$leafName] = $rawPath;
            $this->validLeafs[$leafName] = $leafName;
        }
    }

    protected function readDirectives(): array
    {
        $directives = [];

        foreach (CompositeDir::getRootsIterator() as $rootDir => $rootNamespace) {
            if (is_dir($this->realPathRelativeToTreeRoot)) {
                chdir($this->realPathRelativeToTreeRoot);

                if (is_file('.redirect')) {
                    foreach (explode("\n", file_get_contents('.redirect')) as $line) {
                        $row = explode(' ', $line, 3);
                        if ('' !== $row[0]) {
                            $directives[$row[0]] = isset($row[1]) ? $row[1] : '';
                        }
                    }
                }
            }

        }

        return $directives;
    }
}
