<?php

use \org\bovigo\vfs\vfsStream;
use \sndsgd\File;
use \sndsgd\Path;
use \sndsgd\Temp;



class TempTest extends PHPUnit_Framework_TestCase
{
   /**
    * @covers sndsgd\Temp::registerPath
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
    * @covers sndsgd\Temp::file
    */
   public function testFile()
   {
      $path = Temp::file('test');
      $this->assertTrue(file_exists($path));
      $this->assertEquals(0, filesize($path));

      $contents = 'hello world';
      $path = Temp::file('test', $contents);
      $this->assertTrue(file_exists($path));
      $this->assertEquals($contents, file_get_contents($path));

      $path = Temp::file('test.txt');
      list($name, $ext) = File::splitName($path);
      $this->assertEquals('txt', $ext);
   }

   /**
    * @covers sndsgd\Temp::dir
    */
   public function testDir()
   {
      $path = Temp::dir('test-');
      $this->assertTrue(file_exists($path) && is_dir($path));
   }

   /**
    * @covers sndsgd\Temp::cleanup
    */
   public function testCleanup()
   {
      $this->assertTrue(Temp::cleanup());

      # make a file that cannot be removed and register it for cleanup
      $root = vfsStream::setup('root');
      $dir = vfsStream::newDirectory('test')
         ->at($root)
         ->chmod(0700)
         ->chgrp(vfsStream::GROUP_ROOT)
         ->chown(vfsStream::OWNER_ROOT);
      $file = vfsStream::newFile('file.txt')
         ->setContent('content...')
         ->chmod(0600)
         ->chgrp(vfsStream::GROUP_ROOT)
         ->chown(vfsStream::OWNER_ROOT)
         ->at($dir);

      $path = vfsStream::url($file->path());
      Temp::registerPath($path);
      $this->assertFalse(Temp::cleanup());
   }
}

