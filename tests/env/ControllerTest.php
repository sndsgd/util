<?php

namespace sndsgd\env;

use \sndsgd\Env;


/**
 * @coversDefaultClass \sndsgd\env\Controller
 */
class ControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @coversNothing
     */
    public function setUp()
    {
        $this->controller = new Controller;
        Env::setController($this->controller);
    }

    public function tearDown()
    {
        Env::setController(null);
    }

    public function testToggleStyles()
    {
        # starts at true
        $this->assertAttributeEquals(true, 'showStyles', $this->controller);

        # disable
        $this->controller->disableStyles();
        $this->assertAttributeEquals(false, 'showStyles', $this->controller);

        # re-enable
        $this->controller->enableStyles();
        $this->assertAttributeEquals(true, 'showStyles', $this->controller);
    }

    /**
     * @covers ::extractStyles
     * @covers ::applyStyles
     */
    public function testExtractAndApplyStyles()
    {
        $reflection = new \ReflectionClass(get_class($this->controller));
        $extract = $reflection->getMethod('extractStyles');
        $extract->setAccessible(true);
        $apply = $reflection->getMethod('applyStyles');
        $apply->setAccessible(true);

        # test using the unicode format characters
        $test = "@[ bg:red + bold + white ]";
        $expect = [$test => ['bg:red', 'bold', 'white']];
        $map = $extract->invokeArgs($this->controller, [$test]);
        $this->assertEquals($expect, $map);
        $message = $apply->invokeArgs($this->controller, [$map, $test]);
        $this->assertEquals('', $message);

        # test using the ascii format characters
        $test = "@[bg:red + bold + white ]";
        $expect = [$test => ['bg:red', 'bold', 'white']];
        $map = $extract->invokeArgs($this->controller, [$test]);
        $this->assertEquals($expect, $map);
        $message = $apply->invokeArgs($this->controller, [$map, $test]);
        $this->assertEquals('', $message);

        $one = "@[bg:red]";
        $two = "@[reset+light-cyan]";
        $test = "$one test $two";
        $expect = [
            $one => ['bg:red'],
            $two => ['reset', 'light-cyan']
        ];
        $map = $extract->invokeArgs($this->controller, [$test]);
        $this->assertEquals($expect, $map);
        $message = $apply->invokeArgs($this->controller, [$map, $test]);
        $this->assertEquals(' test ', $message);
    }

    /**
     * @covers ::write
     * @outputBuffering enabled
     */
    public function testWrite()
    {
        $this->controller->write('@[bg:red]test@[reset]');
        $this->expectOutputString("test\n");
    }

    /**
     * @covers ::log
     * @outputBuffering enabled
     */
    public function testLog()
    {
        $this->controller->log('test');
        $this->expectOutputString("test\n");
    }

    /**
     * @covers ::error
     * @outputBuffering enabled
     */
    public function testError()
    {
        $this->controller->error('test', null);
        $this->expectOutputString("test\n");
    }
}