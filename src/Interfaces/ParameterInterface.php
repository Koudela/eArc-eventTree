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

use eArc\Core\Interfaces\ParameterInterface as InterfaceBase;

interface ParameterInterface extends InterfaceBase
{
    /** [root_dir_relative_to_vendor => root_namespace] required */
    const ROOT_DIRECTORIES = 'earc.event_tree.directories';
    /** [fQCN => true] defaults to [] */
    const BLACKLIST = 'earc.event_tree.blacklist';
    /** bool defaults to false */
    const USE_CACHE = 'earc.event_tree.use_cache';
    /** string defaults to '/tmp/earc_event_tree_cache.php' */
    const CACHE_FILE = 'earc.event_tree.cache_file';
    /** bool defaults to true */
    const REPORT_INVALID_OBSERVER_NODE = 'earc.event_tree.report_invalid_observer_node';
}
