<?php

use \sndsgd\Config;


class ConfigTest extends PHPUnit_Framework_TestCase
{
   public static function setUpBeforeClass()
   {
      Config::init(self::$values['init']);
   }

   public static function tearDownAfterClass()
   {
      Config::init([]);
   }

   protected static $values = [
      'init' => [
         'test.one' => 1,
         'test.two' => 2,
         'test.three' => 3,
      ]
   ];

   public function testSet()
   {
      Config::set('test.set.one', 1);
      Config::set([
         'test.set.two' => 2,
         'test.set.three' => 3
      ]);

      $this->assertEquals(1, Config::get('test.set.one'));
      $this->assertEquals(2, Config::get('test.set.two'));
      $this->assertEquals(3, Config::get('test.set.three'));
      $this->assertNull(Config::get('undefined.key'));
   }

   public function testGet()
   {
      # set with Config::init()
      $this->assertEquals(1, Config::get('test.one'));
      $this->assertEquals(2, Config::get('test.two'));
      $this->assertEquals(3, Config::get('test.three'));

      # default value
      $this->assertEquals('noval', Config::get('test.undefined', 'noval'));
   }

   public function testGetRequired()
   {
      $this->assertEquals(1, Config::getRequired('test.one'));
   }

   /**
    * @expectedException \Exception
    */
   public function testGetRequiredException()
   {
      $value = Config::getRequired('test.undefined');
   }

   public function testGetAs()
   {
      $map = [
         'test.one' => 'one',
         'test.two' => 'two'
      ];

      $values = Config::getAs($map);
      $this->assertEquals(1, $values['one']);
      $this->assertEquals(2, $values['two']);

      $map = [
         'test.undefined' => 'shrtval'
      ];
      $expect = "the required config value 'test.undefined' was not found";
      $this->assertEquals($expect, Config::getAs($map));
   }

   public function testGetAll()
   {
      Config::init(self::$values['init']);
      $this->assertEquals(self::$values['init'], Config::getAll());
   }
}


