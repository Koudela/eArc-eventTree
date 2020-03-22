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

namespace eArc\EventTree\Interfaces;

interface ParameterInterface
{
    const VENDOR_DIR = 'earc.vendor_directory';
    const ROOT_DIRECTORIES = 'earc.event_tree.directories';
    const BLACKLIST = 'earc.event_tree.blacklist';
}
