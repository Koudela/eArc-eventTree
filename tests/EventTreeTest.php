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
use eArc\EventTree\Interfaces\TreeEventInterface;
use eArc\EventTree\Propagation\PropagationType;
use eArc\EventTreeTests\env\treeroot\patience\NoPatienceListener;
use eArc\EventTreeTests\env\treeroot\patience\PatienceListener2;
use PHPUnit\Framework\TestCase;

/**
 * This is no unit test. It is an integration test.
 */
class EventTreeTest extends TestCase
{
    public function testIntegration()
    {
        $this->bootstrap();
        $this->runStartDestinationAssertions();
        $this->runDepthAssertions();
        $this->runPatienceAssertions();
        $this->runPhaseAssertions();
        $this->runHandlerAssertions();
        $this->runMultiTreeAssertions();
        $this->runBlacklistAssertions();
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

        $directories = []; //di_param('earc.event_tree.directories', []);
        $directories['../tests/env/treeroot'] = 'eArc\\EventTreeTests\\env\\treeroot';
        di_import_param(['earc' => ['event_tree' => ['directories' => $directories]]]);
    }

    protected function runStartDestinationAssertions()
    {
        $event = new TestEvent(new PropagationType([], [], 0));
        $event->dispatch();
        $this->assertInstanceOf(TreeEventInterface::class, $event);
        $this->assertEquals(['eArc\\EventTreeTests\\env\\treeroot\\BasicListener' => 'treeroot'], $event->isTouchedByListener);
        $event = new TestEvent(new PropagationType(['product', 'export'], ['init', 'collect', 'process', 'finish'], 0));
        $event->dispatch();
        $this->assertEquals([
            'eArc\\EventTreeTests\\env\\treeroot\\product\\export\\BasicListener' => 'export',
            'eArc\\EventTreeTests\\env\\treeroot\\product\\export\\init\\BasicListener' => 'init',
            'eArc\\EventTreeTests\\env\\treeroot\\product\\export\\init\\collect\\BasicListener' => 'collect',
            'eArc\\EventTreeTests\\env\\treeroot\\product\\export\\init\\collect\\process\\BasicListener' => 'process',
            'eArc\\EventTreeTests\\env\\treeroot\\product\\export\\init\\collect\\process\\finish\\BasicListener' => 'finish',
        ], $event->isTouchedByListener);
        $event = new TestEvent(new PropagationType([], ['product', 'export'], 0));
        $event->dispatch();
        $this->assertEquals([
            'eArc\\EventTreeTests\\env\\treeroot\\BasicListener' => 'treeroot',
            'eArc\\EventTreeTests\\env\\treeroot\\product\\BasicListener' => 'product',
            'eArc\\EventTreeTests\\env\\treeroot\\product\\export\\BasicListener' => 'export',
        ], $event->isTouchedByListener);
        $event = new TestEvent(new PropagationType(['product', 'export'], [], 0));
        $event->dispatch();
        $this->assertEquals([
            'eArc\\EventTreeTests\\env\\treeroot\\product\\export\\BasicListener' => 'export',
        ], $event->isTouchedByListener);

    }

    protected function runDepthAssertions()
    {
        $event = new TestEvent(new PropagationType(['leaf1'], ['leaf11'], null));
        $event->dispatch();
        $this->assertEquals([
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\BasicListener' => 'leaf1',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\BasicListener' => 'leaf11',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\BasicListener' => 'leaf111',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\BasicListener' => 'leaf112',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf113\\BasicListener' => 'leaf113',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\leaf1111\\BasicListener' => 'leaf1111',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\BasicListener' => 'leaf1121',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\leaf11211\\BasicListener' => 'leaf11211',
        ], $event->isTouchedByListener);
        $event = new TestEvent(new PropagationType(['leaf1'], ['leaf11'], null));
        $event->dispatch();
        $this->assertEquals([
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\BasicListener' => 'leaf1',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\BasicListener' => 'leaf11',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\BasicListener' => 'leaf111',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\BasicListener' => 'leaf112',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf113\\BasicListener' => 'leaf113',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\leaf1111\\BasicListener' => 'leaf1111',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\BasicListener' => 'leaf1121',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\leaf11211\\BasicListener' => 'leaf11211',
        ], $event->isTouchedByListener);
        $event = new TestEvent(new PropagationType(['leaf1'], ['leaf11'], 11));
        $event->dispatch();
        $this->assertEquals([
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\BasicListener' => 'leaf1',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\BasicListener' => 'leaf11',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\BasicListener' => 'leaf111',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\BasicListener' => 'leaf112',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf113\\BasicListener' => 'leaf113',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\leaf1111\\BasicListener' => 'leaf1111',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\BasicListener' => 'leaf1121',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\leaf11211\\BasicListener' => 'leaf11211',
        ], $event->isTouchedByListener);
        $event = new TestEvent(new PropagationType(['leaf1'], ['leaf11'], 1));
        $event->dispatch();
        $this->assertEquals([
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\BasicListener' => 'leaf1',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\BasicListener' => 'leaf11',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\BasicListener' => 'leaf111',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\BasicListener' => 'leaf112',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf113\\BasicListener' => 'leaf113',
        ], $event->isTouchedByListener);
        $event = new TestEvent(new PropagationType(['leaf1'], ['leaf11'], 2));
        $event->dispatch();
        $this->assertEquals([
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\BasicListener' => 'leaf1',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\BasicListener' => 'leaf11',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\BasicListener' => 'leaf111',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\BasicListener' => 'leaf112',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf113\\BasicListener' => 'leaf113',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\leaf1111\\BasicListener' => 'leaf1111',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\BasicListener' => 'leaf1121',
        ], $event->isTouchedByListener);
    }

    protected function runPatienceAssertions()
    {
        $event = new TestEvent(new PropagationType(['patience'], [],0));
        $event->dispatch();
        $this->assertEquals([
            'eArc\\EventTreeTests\\env\\treeroot\\patience\\PatienceListener2' => 'patience',
            'eArc\\EventTreeTests\\env\\treeroot\\patience\\NoPatienceListener' => 'patience',
            'eArc\\EventTreeTests\\env\\treeroot\\patience\\PatienceListener3' => 'patience',
            'eArc\\EventTreeTests\\env\\treeroot\\patience\\PatienceListener1' => 'patience',
        ], $event->isTouchedByListener);
    }

    protected function runPhaseAssertions()
    {
        $event = new TestEvent(new PropagationType(['phase', 'start'], ['before', 'destination'],null));
        $event->dispatch();
        $this->assertEquals([
            'eArc\\EventTreeTests\\env\\treeroot\\phase\\start\\StartBeyondListener' => 'start',
            'eArc\\EventTreeTests\\env\\treeroot\\phase\\start\\StartListener' => 'start',
            'eArc\\EventTreeTests\\env\\treeroot\\phase\\start\\before\\BeforeListener' => 'before',
            'eArc\\EventTreeTests\\env\\treeroot\\phase\\start\\before\\destination\\DestinationListener' => 'destination',
            'eArc\\EventTreeTests\\env\\treeroot\\phase\\start\\before\\destination\\beyond\\StartBeyondListener' => 'beyond',
            'eArc\\EventTreeTests\\env\\treeroot\\phase\\start\\before\\destination\\beyond\\BeyondListener' => 'beyond',
        ], $event->isTouchedByListener);
    }

    protected function runHandlerAssertions()
    {
        $event = new TestEvent(new PropagationType(['leaf1'], [],null));
        $event->testHandlerAssertions = true;
        $event->dispatch();
        $this->assertEquals([
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\BasicListener' => 'leaf1',
            // tie
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\BasicListener' => 'leaf11',
            // terminate
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\BasicListener' => 'leaf111',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\BasicListener' => 'leaf112',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf113\\BasicListener' => 'leaf113',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\BasicListener' => 'leaf1121',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\leaf11211\\BasicListener' => 'leaf11211',
        ], $event->isTouchedByListener);

        $event = new TestEvent(new PropagationType(['leaf1'], ['leaf12'],null));
        $event->testHandlerAssertions = true;
        $event->dispatch();
        $this->assertEquals([
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\BasicListener' => 'leaf1',
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf12\\BasicListener' => 'leaf12',
            // kill
            'eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf12\\leaf121\\BasicListener' => 'leaf121',
        ], $event->isTouchedByListener);

        $event = new TestEvent(new PropagationType([], ['patience'],null));
        $event->testHandlerAssertions = true;
        $event->dispatch();

        $this->assertEquals([
            // forward (not functional - test: does it not persist)
            'eArc\\EventTreeTests\\env\\treeroot\\BasicListener' => 'treeroot',
            'eArc\\EventTreeTests\\env\\treeroot\\patience\\PatienceListener2' => 'patience',
            // forward
            'eArc\\EventTreeTests\\env\\treeroot\\patience\\NoPatienceListener' => 'patience',
            'eArc\\EventTreeTests\\env\\treeroot\\patience\\forwarded\\BasicListener' => 'forwarded',
        ], $event->isTouchedByListener);
    }

    protected function runMultiTreeAssertions()
    {
        di_clear_cache();

        $directories = []; //di_param('earc.event_tree.directories', []);
        $directories['../tests/env/other/otherTreeRoot'] = 'eArc\\EventTreeTests\\env\\other\\otherTreeRoot';
        di_import_param(['earc' => ['event_tree' => ['directories' => $directories]]]);

        //var_dump($event->isTouchedByListener);
    }

    protected function runBlacklistAssertions()
    {
        di_clear_cache();
        // di_param('...', []);
        di_import_param(['earc' => ['event_tree' => ['blacklist' => [
            NoPatienceListener::class => true,
            PatienceListener2::class => true,
        ]]]]);

        $event = new TestEvent(new PropagationType(['patience'], [],null));
        $event->dispatch();
        $this->assertEquals([
            'eArc\\EventTreeTests\\env\\treeroot\\patience\\PatienceListener3' => 'patience',
            'eArc\\EventTreeTests\\env\\treeroot\\patience\\PatienceListener1' => 'patience',
            'eArc\\EventTreeTests\\env\\treeroot\\patience\\forwarded\\BasicListener' => 'forwarded',
        ], $event->isTouchedByListener);
    }
}