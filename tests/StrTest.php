<?php

use \sndsgd\Str;


class StrTest extends PHPUnit_Framework_TestCase
{
   public function testStartsWith()
   {
      $this->assertTrue(Str::startsWith('hello there', 'he'));
      $this->assertFalse(Str::startsWith('hello there', ' '));
      $this->assertFalse(Str::startsWith('hello there', 'H', true));
   }

   public function testEndsWith()
   {
      $this->assertTrue(Str::endsWith('hello there', 'ere'));
      $this->assertFalse(Str::endsWith('hello there', ' '));
      $this->assertFalse(Str::endsWith('hello there', 'E', true));
      $this->assertTrue(Str::endsWith('hello therE', 'E', true));

      $haystack = 'Wed, 10 Dec 2014 02:54:32 +0000';
      $needle = '+0000';
      $this->assertTrue(Str::endsWith($haystack, $needle));
   }

   public function testRandom()
   {
      # just check the length
      $result = Str::random(100);
      $this->assertEquals(100, strlen($result));
   }

   /**
    * @expectedException InvalidArgumentException
    */
   public function testRandomException()
   {
      $result = Str::random([]);
   }

   public function testToNumber()
   {
      $this->assertEquals(123, Str::toNumber('123'));
      $this->assertEquals(0, Str::toNumber(''));
      $this->assertEquals(-1.23, Str::toNumber('-1.23'));
      $this->assertEquals(-1100, Str::toNumber('-1100'));
      $this->assertEquals(1420, Str::toNumber('1,420.00'));
   }

   public function testToBoolean()
   {
      $tests = [
         ['TRUE', 'assertTrue'],
         ['true', 'assertTrue'],
         ['1', 'assertTrue'],
         [1, 'assertTrue'],
         [true, 'assertTrue'],

         ['FALSE', 'assertFalse'],
         ['false', 'assertFalse'],
         ['0', 'assertFalse'],
         [0, 'assertFalse'],
         [false, 'assertFalse'],
         ['', 'assertFalse'],

         ['jimbo', 'assertNull'],
         [100, 'assertNull'],
         [-100, 'assertNull']
      ];

      foreach ($tests as $test) {
         list($test, $method) = $test;
         $result = Str::toBoolean($test);
         $err = 
            "test: ".var_export($test, true).
            " result: ".var_export($result, true);
         $this->$method($result, $err);
      }
   }

   public function testToCamelCase()
   {
      $tests = [
         ['camel-case', 'camelCase'],
         ['camel_case', 'camelCase'],
         ['camel case', 'camelCase'],
         [' camel case_long', 'camelCaseLong'],
      ];

      foreach ($tests as $test) {
         list($test, $expect) = $test;
         $this->assertEquals($expect, Str::toCamelCase($test)); 
      }
   }

   public function testToSnakeCase()
   {
      $tests = [
         [' snake-case', 'snake_case'],
         ['snake--case', 'snake_case'],
         ['snake- -case', 'snake_case'],
         ['snake case', 'snake_case'],
         ['snakeCase', 'snake_case'],
         ['snakeCaseLong', 'snake_case_long']
      ];
      
      foreach ($tests as $test) {
         list($test, $expect) = $test;
         $this->assertEquals($expect, Str::toSnakeCase($test));   
      }

      $this->assertEquals('SNAKE_CASE', Str::toSnakeCase('snakeCase', true));
   }

   public function testStripPostNewlineTabs()
   {
      $tests = [
         "one\n\ttwo\n\t\t\tthree" => "one\ntwo\nthree",
         "one\n\t\t\ntwo" => "one\n\ntwo"
      ];

      foreach ($tests as $test => $expect) {
         $result = Str::stripPostNewlineTabs($test);
         $this->assertEquals($expect, $result);
      }
   }

   public function testStripEmptyLines()
   {
      $tests = [
         "one\n\ntwo" => "one\ntwo",
         "one\n \ntwo\n\t\nthree\n \t \nfour" => "one\ntwo\nthree\nfour"
      ];

      foreach ($tests as $test => $expect) {
         $result = Str::stripEmptyLines($test);
         $this->assertEquals($expect, $result);
      }
   }
}

