<?php

use \sndsgd\Memcached;


class MemcachedTest extends PHPUnit_Framework_TestCase
{
   public function test()
   {
      if (!extension_loaded('memcached')) {
         return;
      }

      $mc = new Memcached;
      if ($mc->set('test-integer', 42)) {
         $this->assertEquals(42, $mc->get('test-integer'));   
      }
      if ($mc->set('test-float', 4.2)) {
         $this->assertEquals(4.2, $mc->get('test-float'));   
      }
      if ($mc->set('test-string', 'hello world')) {
         $this->assertEquals('hello world', $mc->get('test-string'));
      }
   }
}

