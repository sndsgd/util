<?php

use \sndsgd\util\Classname;


class ClassnameTest extends PHPUnit_Framework_TestCase
{
   public function testSplit()
   {
      $res = Classname::split('one.two.three');
      $this->assertEquals(['one', 'two', 'three'], $res);
   }

   /**
    * @expectedException InvalidArgumentException
    */
   public function testSplitException()
   {
      Classname::split([]);
   }

   public function testToString()
   {
      $res = Classname::toString('one.two.three');
      $this->assertEquals('one\\two\\three', $res);

      $res = Classname::toString('one\\two\\three', '.');
      $this->assertEquals('one.two.three', $res);

      $res = Classname::toString(['one','two','three']);
      $this->assertEquals('one\\two\\three', $res);      
   }

   public function testToMethod()
   {
      $res = Classname::toMethod('some.namespace.method');
      $this->assertEquals('some\\namespace::method', $res);

      $res = Classname::toMethod(['some','namespace','method']);
      $this->assertEquals('some\\namespace::method', $res);      
   }
}


