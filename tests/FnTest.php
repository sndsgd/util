<?php

use \sndsgd\Fn;


class FnTest extends PHPUnit_Framework_TestCase
{
   public function testExists()
   {
      $this->assertTrue(Fn::exists('is_string') !== null);
      $this->assertTrue(Fn::exists('filesize') !== null);
      $this->assertFalse(Fn::exists('_____________nope') !== null);

      $this->assertTrue(Fn::exists('DateTime::createFromFormat') !== null);
      $this->assertTrue(Fn::exists('sndsgd\\Fn::exists') !== null);
      $this->assertFalse(Fn::exists('This\\Class\\Doesnt\\Exist::test') !== null);
   }
}

