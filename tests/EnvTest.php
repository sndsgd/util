<?php

namespace sndsgd\env;

use \sndsgd\Env;


/**
 * A controller that removes all styles and echos messages
 * Used to test sndsgd\Env::log && sndsgd\Env::error
 *
 * @coversNothing
 */
class TestController extends \sndsgd\env\Controller
{
    public function write($message, $code = 0)
    {
        echo $message;
    }
}

/**
 * @coversDefaultClass \sndsgd\Env
 */
class EnvTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @coversNothing
     */
    public function setUp()
    {
        $this->class = new \ReflectionClass("sndsgd\\Env");
        $this->controller = new TestController;
        Env::setController($this->controller);
    }

    public function tearDown()
    {
        Env::setController(null);
    }

    /**
     * @coversNothing
     */
    private function getStaticProperty($name)
    {
        $property = $this->class->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }

    /**
     * @coversNothing
     */
    private function getStaticMethod($name)
    {
        $method = $this->class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @covers ::setController
     * @covers ::getController
     */
    public function testSetGetController()
    {
        $property = $this->getStaticProperty("controller");
        $controller = Env::getController();
        $this->assertInstanceOf("sndsgd\\env\\Controller", $controller);

        Env::setController(null);
        $this->assertNull($property->getValue());
        $this->assertNull(Env::getController());

        Env::setController($this->controller);
        $this->assertInstanceOf("sndsgd\\env\\Controller", $property->getValue());
    }

    /**
     * @covers ::setVerboseLevel
     * @covers ::getVerboseLevel
     * @covers ::validateVerboseLevel
     */
    public function testSetVerboseLevel()
    {
        $property = $this->getStaticProperty("verboseLevel");

        $levels = [
            Env::QUIET,
            Env::NORMAL,
            Env::V,
            Env::VV,
            Env::VVV
        ];

        foreach ($levels as $level) {
            Env::setVerboseLevel($level);
            $this->assertEquals($level, Env::getVerboseLevel());
        }
    }

    /**
     * @covers ::setVerboseLevel
     * @covers ::validateVerboseLevel
     * @expectedException InvalidArgumentException
     */
    public function testSetVerboseLevelException()
    {
        Env::setVerboseLevel("42");
    }

    /**
     * @covers ::validateMessage
     */
    public function testValidateMessage()
    {
        $method = $this->getStaticMethod("validateMessage");

        $msg = "test message";
        $this->assertEquals($msg, $method->invokeArgs($this->class, [$msg]));

        $func = function() use ($msg) { return $msg; };
        $this->assertEquals($msg, $method->invokeArgs($this->class, [$func]));
    }

    /**
     * @covers ::validateMessage
     * @expectedException InvalidArgumentException
     */
    public function testValidateMessageException()
    {
        $method = $this->getStaticMethod("validateMessage");
        $method->invokeArgs($this->class, [new \StdClass]);
    }

    /**
     * @covers ::log
     * @outputBuffering enabled
     */
    public function testLogVerboseQuiet()
    {
        Env::setVerboseLevel(Env::QUIET);
        Env::log("0", Env::NORMAL);
        Env::log("1", Env::V);
        Env::log("2", Env::VV);
        Env::log("3", Env::VVV);
        $this->expectOutputString("");
    }

    /**
     * @covers ::log
     * @outputBuffering enabled
     */
    public function testLogVerboseNormal()
    {
        Env::setVerboseLevel(Env::NORMAL);
        Env::log("0", Env::NORMAL);
        Env::log("1", Env::V);
        Env::log("2", Env::VV);
        Env::log("3", Env::VVV);
        $this->expectOutputString("0");
    }

    /**
     * @covers ::log
     * @outputBuffering enabled
     */
    public function testLogVerboseOne()
    {
        Env::setVerboseLevel(Env::V);
        Env::log("0", Env::NORMAL);
        Env::log("1", Env::V);
        Env::log("2", Env::VV);
        Env::log("3", Env::VVV);
        $this->expectOutputString("01");
    }

    /**
     * @covers ::log
     * @outputBuffering enabled
     */
    public function testLogVerboseTwo()
    {
        Env::setVerboseLevel(Env::VV);
        Env::log("0", Env::NORMAL);
        Env::log("1", Env::V);
        Env::log("2", Env::VV);
        Env::log("3", Env::VVV);
        $this->expectOutputString("012");
    }

    /**
     * @covers ::log
     * @outputBuffering enabled
     */
    public function testLogVerboseThree()
    {
        Env::setVerboseLevel(Env::VVV);
        Env::log("0", Env::NORMAL);
        Env::log("1", Env::V);
        Env::log("2", Env::VV);
        Env::log("3", Env::VVV);
        $this->expectOutputString("0123");
    }

    /**
     * @covers ::log
     * @expectedException InvalidArgumentException
     */
    public function testLogInvalidLevel()
    {
        Env::log("0", Env::QUIET);
    }

    /**
     * @covers ::error
     * @outputBuffering enabled
     */
    public function testError()
    {
        Env::error("test", null);
        $this->expectOutputString("test");
    }

    /**
     * @covers ::error
     */
    public function testErrorTrigger()
    {
        $class = "sndsgd\\env\\Controller";
        $controller = $this->getMockBuilder($class)->getMock();
        $controller->method("terminate")->willReturn(true);
        Env::setController($controller);
        Env::error("test message", 1);
    }

    /**
     * @covers ::terminate
     */
    public function testTerminate()
    {
        $class = "sndsgd\\env\\Controller";
        $controller = $this->getMockBuilder($class)->getMock();
        $controller->method("terminate")->willReturn(true);
        Env::setController($controller);
        Env::terminate(1);
    }
}