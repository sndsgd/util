<?php

use \sndsgd\util\Temp;


class TempTest extends PHPUnit_Framework_TestCase
{
   /**
    * @covers sndsgd\util\Temp::registerPath
    */
   public function testRegisterPath()
   {
      Temp::cleanup();
      $path = tempnam(sys_get_temp_dir(), 'test-file-');
      $path2 = tempnam(sys_get_temp_dir(), 'test-file-');
      Temp::registerPath($path);
      Temp::registerPath($path2);
      Temp::cleanup();
   }

   /**
    * @covers sndsgd\util\Temp::file
    */
   public function testFile()
   {
      $path = Temp::file('test-');
      $this->assertTrue(file_exists($path));
      $this->assertEquals(0, filesize($path));

      $contents = 'hello world';
      $path = Temp::file('test-', $contents);
      $this->assertTrue(file_exists($path));
      $this->assertEquals($contents, file_get_contents($path));
   }

   /**
    * @covers sndsgd\util\Temp::dir
    */
   public function testDir()
   {
      $path = Temp::dir('test-');
      $this->assertTrue(file_exists($path) && is_dir($path));
   }

   /**
    * @covers sndsgd\util\Temp::cleanup
    */
   public function testCleanup()
   {
      $this->assertTrue(Temp::cleanup());
   }
}

