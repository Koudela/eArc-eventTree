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

use eArc\EventTree\Util\Model\Redirect;

class DirectiveReader
{
    /**
     * @param string $realPathRelativeToTreeRoot
     * @return string[]
     */
    public static function getLookup(string $realPathRelativeToTreeRoot): array
    {
        $lookup = [$realPathRelativeToTreeRoot];

        foreach (CompositeDir::getRootsIterator() as $rootDir => $rootNamespace) {
            if (!is_dir($realPathRelativeToTreeRoot)) {
                continue;
            }

            chdir($realPathRelativeToTreeRoot);

            if (is_file('.lookup')) {
                foreach (explode("\n", file_get_contents('.lookup')) as $line) {
                    $lookup[] = trim($line);
                }
            }
        }

        return $lookup;
    }

    /**
     * @param string $realPathRelativeToTreeRoot
     *
     * @return Redirect
     */
    public static function getRedirect(string $realPathRelativeToTreeRoot): Redirect
    {
        return new Redirect($realPathRelativeToTreeRoot);
    }
}
