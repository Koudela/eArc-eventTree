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

namespace eArc\EventTreeTests;

use eArc\Core\Configuration;
use eArc\DI\DI;
use eArc\DI\Exceptions\InvalidArgumentException;
use eArc\EventTree\Exceptions\IsDispatchedException;
use eArc\EventTree\Interfaces\ParameterInterface;
use eArc\EventTree\Interfaces\TreeEventInterface;
use eArc\EventTree\Propagation\PropagationType;
use eArc\EventTreeTests\env\BaseListener;
use eArc\EventTreeTests\env\TestEvent;
use PHPUnit\Framework\TestCase;

/**
 * This is no unit test. It is an integration test.
 */
class EventTreeTest extends TestCase
{
     /**
     * @throws IsDispatchedException
     * @throws InvalidArgumentException
     */
    public function testStartDestinationAssertions()
    {
        $this->bootstrap();

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType([], [], 0));
        $event->dispatch();
        $this->assertInstanceOf(TreeEventInterface::class, $event);
        $this->assertEquals(['0_eArc\\EventTreeTests\\env\\treeroot\\BasicListener' => null], $event->isTouchedByListener);

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['blank'], [], null));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\EventTreeTests\env\treeroot\blank\otherBlank\done\BasicListener' => 'done'
        ], $event->isTouchedByListener);


        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['product', 'export'], ['init', 'collect', 'process', 'finish'], 0));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\\EventTreeTests\\env\\treeroot\\product\\export\\BasicListener' => 'export',
            '1_eArc\\EventTreeTests\\env\\treeroot\\product\\export\\init\\BasicListener' => 'init',
            '2_eArc\\EventTreeTests\\env\\treeroot\\product\\export\\init\\collect\\BasicListener' => 'collect',
            '3_eArc\\EventTreeTests\\env\\treeroot\\product\\export\\init\\collect\\process\\BasicListener' => 'process',
            '4_eArc\\EventTreeTests\\env\\treeroot\\product\\export\\init\\collect\\process\\finish\\BasicListener' => 'finish',
        ], $event->isTouchedByListener);

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType([], ['product', 'export'], 0));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\\EventTreeTests\\env\\treeroot\\BasicListener' => null,
            '1_eArc\\EventTreeTests\\env\\treeroot\\product\\BasicListener' => 'product',
            '2_eArc\\EventTreeTests\\env\\treeroot\\product\\export\\BasicListener' => 'export',
        ], $event->isTouchedByListener);

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['product', 'export'], [], 0));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\\EventTreeTests\\env\\treeroot\\product\\export\\BasicListener' => 'export',
        ], $event->isTouchedByListener);

    }

    /**
     * @throws IsDispatchedException
     * @throws InvalidArgumentException
     */
    public function testDepthAssertions()
    {
        $this->bootstrap();

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['leaf1'], ['leaf11'], null));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\BasicListener' => 'leaf1',
            '1_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\BasicListener' => 'leaf11',
            '2_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\BasicListener' => 'leaf111',
            '3_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\BasicListener' => 'leaf112',
            '4_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf113\\BasicListener' => 'leaf113',
            '5_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\leaf1111\\BasicListener' => 'leaf1111',
            '6_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\BasicListener' => 'leaf1121',
            '7_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\leaf11211\\BasicListener' => 'leaf11211',
        ], $event->isTouchedByListener);

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['leaf1'], ['leaf11'], null));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\BasicListener' => 'leaf1',
            '1_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\BasicListener' => 'leaf11',
            '2_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\BasicListener' => 'leaf111',
            '3_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\BasicListener' => 'leaf112',
            '4_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf113\\BasicListener' => 'leaf113',
            '5_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\leaf1111\\BasicListener' => 'leaf1111',
            '6_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\BasicListener' => 'leaf1121',
            '7_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\leaf11211\\BasicListener' => 'leaf11211',
        ], $event->isTouchedByListener);

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['leaf1'], ['leaf11'], 11));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\BasicListener' => 'leaf1',
            '1_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\BasicListener' => 'leaf11',
            '2_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\BasicListener' => 'leaf111',
            '3_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\BasicListener' => 'leaf112',
            '4_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf113\\BasicListener' => 'leaf113',
            '5_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\leaf1111\\BasicListener' => 'leaf1111',
            '6_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\BasicListener' => 'leaf1121',
            '7_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\leaf11211\\BasicListener' => 'leaf11211',
        ], $event->isTouchedByListener);

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['leaf1'], ['leaf11'], 1));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\BasicListener' => 'leaf1',
            '1_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\BasicListener' => 'leaf11',
            '2_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\BasicListener' => 'leaf111',
            '3_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\BasicListener' => 'leaf112',
            '4_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf113\\BasicListener' => 'leaf113',
        ], $event->isTouchedByListener);

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['leaf1'], ['leaf11'], 2));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\BasicListener' => 'leaf1',
            '1_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\BasicListener' => 'leaf11',
            '2_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\BasicListener' => 'leaf111',
            '3_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\BasicListener' => 'leaf112',
            '4_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf113\\BasicListener' => 'leaf113',
            '5_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\leaf1111\\BasicListener' => 'leaf1111',
            '6_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\BasicListener' => 'leaf1121',
        ], $event->isTouchedByListener);
    }

    /**
     * @throws IsDispatchedException
     * @throws InvalidArgumentException
     */
    public function testPatienceAssertions()
    {
        $this->bootstrap();

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['patience'], [],0));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\\EventTreeTests\\env\\treeroot\\patience\\PatienceListener2' => 'patience',
            '1_eArc\\EventTreeTests\\env\\treeroot\\patience\\NoPatienceListener' => 'patience',
            '2_eArc\\EventTreeTests\\env\\treeroot\\patience\\PatienceListener3' => 'patience',
            '3_eArc\\EventTreeTests\\env\\treeroot\\patience\\PatienceListener1' => 'patience',
        ], $event->isTouchedByListener);
    }

    /**
     * @throws IsDispatchedException
     * @throws InvalidArgumentException
     */
    public function testPhaseAssertions()
    {
        $this->bootstrap();

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['phase', 'start'], ['before', 'destination'],null));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\\EventTreeTests\\env\\treeroot\\phase\\start\\StartBeyondListener' => 'start',
            '1_eArc\\EventTreeTests\\env\\treeroot\\phase\\start\\StartListener' => 'start',
            '2_eArc\\EventTreeTests\\env\\treeroot\\phase\\start\\before\\BeforeListener' => 'before',
            '3_eArc\\EventTreeTests\\env\\treeroot\\phase\\start\\before\\destination\\DestinationListener' => 'destination',
            '4_eArc\\EventTreeTests\\env\\treeroot\\phase\\start\\before\\destination\\beyond\\BeyondListener' => 'beyond',
            '5_eArc\\EventTreeTests\\env\\treeroot\\phase\\start\\before\\destination\\beyond\\StartBeyondListener' => 'beyond',
        ], $event->isTouchedByListener);
    }

    /**
     * @throws IsDispatchedException
     * @throws InvalidArgumentException
     */
    public function testHandlerAssertions()
    {
        $this->bootstrap();

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['leaf1'], [],null));
        $event->testHandlerAssertions = true;
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\BasicListener' => 'leaf1',
            // tie
            '1_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\BasicListener' => 'leaf11',
            // terminate
            '2_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf111\\BasicListener' => 'leaf111',
            '3_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\BasicListener' => 'leaf112',
            '4_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf113\\BasicListener' => 'leaf113',
            '5_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\BasicListener' => 'leaf1121',
            '6_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf11\\leaf112\\leaf1121\\leaf11211\\BasicListener' => 'leaf11211',
        ], $event->isTouchedByListener);

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['leaf1'], ['leaf12'],null));
        $event->testHandlerAssertions = true;
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\BasicListener' => 'leaf1',
            '1_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf12\\BasicListener' => 'leaf12',
            // kill
            '2_eArc\\EventTreeTests\\env\\treeroot\\leaf1\\leaf12\\leaf121\\BasicListener' => 'leaf121',
        ], $event->isTouchedByListener);

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType([], ['patience'],null));
        $event->testHandlerAssertions = true;
        $event->dispatch();
        $this->assertEquals([
            // forward (not functional - test: does it not persist)
            '0_eArc\\EventTreeTests\\env\\treeroot\\BasicListener' => null,
            '1_eArc\\EventTreeTests\\env\\treeroot\\patience\\PatienceListener2' => 'patience',
            // forward
            '2_eArc\\EventTreeTests\\env\\treeroot\\patience\\NoPatienceListener' => 'patience',
            '3_eArc\\EventTreeTests\\env\\treeroot\\patience\\forwarded\\BasicListener' => 'forwarded',
        ], $event->isTouchedByListener);
    }

    /**
     * @throws IsDispatchedException
     * @throws InvalidArgumentException
     */
    public function testMultiTreeAssertions()
    {
        $this->bootstrap('.earc-multi-tree-config.php');

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['patience'], [],null));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\\EventTreeTests\\env\\treeroot\\patience\\PatienceListener2' => 'patience',
            '1_eArc\\EventTreeTests\\env\\treeroot\\patience\\NoPatienceListener' => 'patience',
            '2_eArc\\EventTreeTests\\env\\other\\otherTreeRoot\\patience\\BasicListener' => 'patience',
            '3_eArc\\EventTreeTests\\env\\treeroot\\patience\\PatienceListener3' => 'patience',
            '4_eArc\\EventTreeTests\\env\\treeroot\\patience\\PatienceListener1' => 'patience',
            '5_eArc\\EventTreeTests\\env\\other\\otherTreeRoot\\patience\\aNewFolder\\BasicListener' => 'aNewFolder',
            '6_eArc\\EventTreeTests\\env\\treeroot\\patience\\forwarded\\BasicListener' => 'forwarded',
            '7_eArc\\EventTreeTests\\env\\other\\otherTreeRoot\\patience\\newInOtherTree\\BasicListener' => 'newInOtherTree',
        ], $event->isTouchedByListener);
    }

    /**
     * @throws IsDispatchedException
     * @throws InvalidArgumentException
     */
    public function testBlacklistAssertions()
    {
        $this->bootstrap('.earc-blacklist-config.php');
        di_clear_cache();

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['patience'], [],null));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\\EventTreeTests\\env\\other\\otherTreeRoot\\patience\\BasicListener' => 'patience',
            '1_eArc\\EventTreeTests\\env\\treeroot\\patience\\PatienceListener3' => 'patience',
            '2_eArc\\EventTreeTests\\env\\treeroot\\patience\\PatienceListener1' => 'patience',
            '3_eArc\\EventTreeTests\\env\\other\\otherTreeRoot\\patience\\aNewFolder\\BasicListener' => 'aNewFolder',
            '4_eArc\\EventTreeTests\\env\\treeroot\\patience\\forwarded\\BasicListener' => 'forwarded',
        ], $event->isTouchedByListener);
    }

    /**
     * @throws IsDispatchedException
     * @throws InvalidArgumentException
     */
    public function testRedirectDirectiveAssertions()
    {
        $this->bootstrap('.earc-multi-tree-config.php');
        di_clear_cache();

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['panda'], [],null));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\\EventTreeTests\\env\\treeroot\\redirect\\BasicListener' => 'panda',
            '1_eArc\\EventTreeTests\\env\\other\\otherTreeRoot\\redirect\\BasicListener' => 'panda',
            '2_eArc\\EventTreeTests\\env\\treeroot\\redirect\\products\\BasicListener' => 'imported',
            '3_eArc\\EventTreeTests\\env\\other\\otherTreeRoot\\redirect\\products\\BasicListener' => 'imported',
            '4_eArc\\EventTreeTests\\env\\other\\otherTreeRoot\\redirect\\some\\BasicListener' => 'some',
            '5_eArc\\EventTreeTests\\env\\treeroot\\redirect\\products\\imported\\BasicListener' => 'products',
            '6_eArc\\EventTreeTests\\env\\other\\otherTreeRoot\\redirect\\products\\imported\\BasicListener' => 'products',
            '7_eArc\\EventTreeTests\\env\\other\\otherTreeRoot\\redirect\\some\\other\\BasicListener' => 'other',
            '8_eArc\\EventTreeTests\\env\\treeroot\\redirect\\products\\imported\\done\\BasicListener' => 'done',
            '9_eArc\EventTreeTests\env\other\otherTreeRoot\redirect\some\BasicListener' => 'link',
            '10_eArc\EventTreeTests\env\other\otherTreeRoot\redirect\some\other\BasicListener' => 'other',
        ], $event->isTouchedByListener);

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType([], ['panda', 'imported', 'products'],null));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\EventTreeTests\env\treeroot\BasicListener' => null,
            '1_eArc\EventTreeTests\env\other\otherTreeRoot\BasicListener' => null,
            '2_eArc\\EventTreeTests\\env\\treeroot\\redirect\\BasicListener' => 'panda',
            '3_eArc\\EventTreeTests\\env\\other\\otherTreeRoot\\redirect\\BasicListener' => 'panda',
            '4_eArc\\EventTreeTests\\env\\treeroot\\redirect\\products\\BasicListener' => 'imported',
            '5_eArc\\EventTreeTests\\env\\other\\otherTreeRoot\\redirect\\products\\BasicListener' => 'imported',
            '6_eArc\\EventTreeTests\\env\\treeroot\\redirect\\products\\imported\\BasicListener' => 'products',
            '7_eArc\\EventTreeTests\\env\\other\\otherTreeRoot\\redirect\\products\\imported\\BasicListener' => 'products',
            '8_eArc\\EventTreeTests\\env\\treeroot\\redirect\\products\\imported\\done\\BasicListener' => 'done',
            '9_eArc\\EventTreeTests\\env\\other\\otherTreeRoot\\redirect\\some\\BasicListener' => 'link',
            '10_eArc\\EventTreeTests\\env\\other\\otherTreeRoot\\redirect\\some\\other\\BasicListener' => 'other',
        ], $event->isTouchedByListener);
    }

    /**
     * @throws IsDispatchedException
     * @throws InvalidArgumentException
     */
    public function testLookupDirectiveAssertions()
    {
        $this->bootstrap('.earc-multi-tree-config.php');

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['lookup'], [], null));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\EventTreeTests\env\treeroot\lookup\BasicListener' => 'lookup',
            '1_eArc\EventTreeTests\env\other\otherTreeRoot\redirect\some\other\BasicListener' => 'lookup',
            '2_eArc\EventTreeTests\env\treeroot\product\BasicListener' => 'lookup',
        ], $event->isTouchedByListener);
    }

    /**
     * @throws InvalidArgumentException
     * @throws IsDispatchedException
     */
    public function testCacheAssertions()
    {
        $this->bootstrap('.earc-cache-config.php');

        exec('rm /tmp/earc_event_tree_cache.php');

        $this->assertFalse(is_file('/tmp/earc_event_tree_cache.php'));

        BaseListener::$i = 0;
        $event = new TestEvent(new PropagationType(['lookup'], [], null));
        $event->dispatch();
        $this->assertEquals([
            '0_eArc\EventTreeTests\env\treeroot\lookup\BasicListener' => 'lookup',
            '1_eArc\EventTreeTests\env\other\otherTreeRoot\redirect\some\other\BasicListener' => 'lookup',
            '2_eArc\EventTreeTests\env\treeroot\product\BasicListener' => 'lookup',
        ], $event->isTouchedByListener);

        $this->assertTrue(is_file('/tmp/earc_event_tree_cache.php'));

        exec(__DIR__.'/../tools/build-cache '.di_param(ParameterInterface::CONFIG_FILE));
        exec(__DIR__.'/../tools/build-cache '.di_param(ParameterInterface::CONFIG_FILE));
    }

    /**
     * @param string|null $configFile
     *
     * @throws InvalidArgumentException
     */
    protected function bootstrap(?string $configFile = null)
    {
        $vendorDir = dirname(__DIR__).'/vendor';

        if (!is_dir($vendorDir)) {
            $vendorDir = dirname(__DIR__, 3);
        }

        require_once $vendorDir.'/autoload.php';

        DI::init();
        di_clear_cache();
        di_import_param(['earc' => null]);
        Configuration::build(__DIR__.'/env/'.($configFile ?? '.earc-config.php'));
    }
}

