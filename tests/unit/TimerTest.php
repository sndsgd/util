<?php

namespace sndsgd;

class TimerTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        Timer::reset();
    }

    public function testReset()
    {
        $one = new Timer("one");
        $two = new Timer("two");
        $three = new Timer("three");
        $this->assertCount(3, Timer::getDurations());
        Timer::reset();
        $this->assertCount(0, Timer::getDurations());
    }

    public function testToString()
    {
        $name = "test";
        $timer = new Timer($name);
        $msg = (string) $timer;
        $this->assertTrue(
            strpos($msg, $name) === 0 &&
            strpos($msg, "has consumed") !== false
        );

        $timer->stop();
        $msg = (string) $timer;
        $this->assertTrue(
            strpos($msg, $name) === 0 &&
            strpos($msg, "took") !== false
        );
    }

    public function testGetDurations()
    {
        $one = new Timer("one");
        $two = new Timer("two");
        $three = new Timer("three");
        $durations = Timer::getDurations();

        $expect = ["one", "two", "three"];
        $this->assertEquals($expect, array_keys($durations));

        usleep(100);
        $shortest = $three->stop();
        usleep(100);
        $shorter = $two->stop();
        usleep(100);
        $short = $one->stop();

        $this->assertTrue($short > $shortest);
        $this->assertTrue($short > $shorter);
    }

    public function testGetName()
    {
        $name = "test";
        $timer = new Timer($name);
        $this->assertEquals($name, $timer->getName());
    }

    public function testRestart()
    {
        $timer = new Timer("test");
        $time = $timer->getStartTime();
        usleep(10);
        $timer->restart();
        $this->assertTrue($time < $timer->getStartTime());
    }

    public function testGetStartTime()
    {
        $timer = new Timer("test");
        $time = $timer->getStartTime();
        $this->assertTrue($time < microtime(true));
        $this->assertTrue(is_float($time));
    }

    public function testGetStopTime()
    {
        $timer = new Timer("test");
        $this->assertNull($timer->getStopTime());
        $this->assertTrue(is_float($timer->stop()));
        $this->assertTrue(is_float($timer->getStopTime()));
    }

    public function testGetDuration()
    {
        $timer = new Timer("test");
        $time = $timer->getDuration();
        $this->assertTrue(is_float($time));

        $time = $timer->stop();
        $precision = 5;
        $expect = number_format($time, $precision);
        $this->assertEquals($expect, $timer->getDuration($precision));
        $this->assertTrue(preg_match("~[0-9]+\.[0-9]+~", $time) === 1);
    }
}
