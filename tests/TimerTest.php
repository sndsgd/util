<?php

namespace sndsgd;


class TimerTest extends \PHPUnit_Framework_TestCase
{
   public function setUp()
   {
      Timer::reset();
      $this->timer = new Timer;
      $this->timer->start();
   }

   public function testReset()
   {
      $one = Timer::create('one');
      $two = Timer::create('two');
      $three = Timer::create('three');
      $this->assertCount(3, Timer::exportDurations());
      Timer::reset();
      $this->assertCount(0, Timer::exportDurations());
   }

   public function testToString()
   {

      $msg = "$this->timer";
      $this->assertTrue(
         strpos($msg, 'task') === 0 &&
         strpos($msg, 'has consumed') !== false
      );

      $this->timer->setName('test');
      $this->timer->stop();
      $msg = "$this->timer";
      $this->assertTrue(
         strpos($msg, 'test') === 0 &&
         strpos($msg, 'took') !== false
      );
   }

   /**
    * @expectedException Exception
    */
   public function testStartException()
   {
      $timer = new Timer;
      $timer->stop();
   }

   public function testExportDurations()
   {
      $one = Timer::create('one');
      $two = Timer::create('two');
      $three = Timer::create('three');
      $durations = Timer::exportDurations();

      $expect = ['one', 'two', 'three'];
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

   public function testCreate()
   {
      $name = 'test';
      $timer = Timer::create($name);
      $this->assertInstanceOf('sndsgd\\Timer', $timer);
      $this->assertEquals($name, $timer->getName());

      usleep(100000);
      $duration = $timer->stop();
      $this->assertGreaterThan(.1, $duration);
   }

   public function testSetName()
   {
      $name = 'test';
      $this->timer->setName($name);
      $durations = Timer::exportDurations();
      $this->assertTrue(array_key_exists($name, $durations));

      $newName = 'new-name';
      $this->timer->setName($newName);
      $durations = Timer::exportDurations();
      $this->assertFalse(array_key_exists($name, $durations));
      $this->assertTrue(array_key_exists($newName, $durations));
   }

   /**
    * @expectedException InvalidArgumentException
    */
   public function testSetNameException()
   {
      Timer::create('one');
      Timer::create('one');
   }

   public function testGetName()
   {
      $this->assertNull($this->timer->getName());
      $name = 'test';
      $this->timer->setName($name);
      $this->assertEquals($name, $this->timer->getName());
   }

   public function testGetStartTime()
   {
      $time = $this->timer->getStartTime();
      $this->assertTrue($time < microtime(true));
      $this->assertTrue(is_float($time));
   }

   public function testGetStopTime()
   {
      $this->assertNull($this->timer->getStopTime());
      $this->assertTrue(is_float($this->timer->stop()));
      $this->assertTrue(is_float($this->timer->getStopTime()));
   }

   public function testGetDuration()
   {
      $time = $this->timer->getDuration();
      $this->assertTrue(is_float($time));

      $time = $this->timer->stop();
      $precision = 5;
      $expect = number_format($time, $precision);
      $this->assertEquals($expect, $this->timer->getDuration($precision));
      $this->assertTrue(preg_match('/[0-9]+\.[0-9]+/', $time) === 1);
   }

   /**
    * @expectedException InvalidArgumentException
    */
   public function testGetDurationInvalidPrecisionException()
   {
      $this->timer->getDuration("42");
   }
}

