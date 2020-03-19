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

namespace eArc\EventTreeTests;

use eArc\DI\DI;
use eArc\DI\Exceptions\InvalidArgumentException;
use eArc\EventTree\Propagation\PropagationType;
use eArc\EventTree\TreeEvent;
use PHPUnit\Framework\TestCase;

/**
 * This is no unit test. It is an integration test.
 */
class EventTreeTest extends TestCase
{
    public function testIntegration()
    {
        $this->bootstrap();
        $this->runSomeAssertions();
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function bootstrap()
    {
        $vendorDir = dirname(__DIR__).'/vendor';

        if (!is_dir($vendorDir)) {
            $vendorDir = dirname(__DIR__, 3);
        }

        require_once $vendorDir.'/autoload.php';

        DI::init();

        di_import_param(['earc' => ['vendor_directory' => $vendorDir]]);

        $directories = di_param('earc.event_tree.directories', []);
        $directories['../tests/env/treeroot'] = 'eArc\\EventTreeTests\\env\\treeroot';
        di_import_param(['earc' => ['event_tree' => ['directories' => $directories]]]);

        (new TreeEvent(new PropagationType([], ['leaf1', 'leaf12'], 0)))->dispatch();
    }

    protected function runSomeAssertions()
    {
        di_clear_cache();
    }
}