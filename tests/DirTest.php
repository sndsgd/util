<?php

use \org\bovigo\vfs\vfsStream;
use \sndsgd\Dir;
use \sndsgd\Path;
use \sndsgd\Temp;


class DirTest extends PHPUnit_Framework_TestCase
{
   /**
    * @coversNothing
    */
   protected function setUp()
   {
      $this->root = vfsStream::setup('root');
      vfsStream::create([
         'test' => [
            'file1.txt' => 'contents...',
            'emptydir' => [
            ]
         ],
         'noreadwrite' => [],
         'empty' => [],
         'rmfilefail' => [
            'file.txt' => 'contents'
         ],

         # 
         'rmdirfail' => [
            'sub' => [],
            'file.txt' => 'contents'
         ]
      ]);

      chmod(vfsStream::url('root/noreadwrite'), 0700);
      $this->root->getChild('noreadwrite')
         ->chown(vfsStream::OWNER_ROOT)
         ->chgrp(vfsStream::GROUP_ROOT);

      chmod(vfsStream::url('root/rmfilefail/file.txt'), 0700);
      $this->root->getChild('rmfilefail')->getChild('file.txt')
         ->chown(vfsStream::OWNER_ROOT)
         ->chgrp(vfsStream::GROUP_ROOT);

      chmod(vfsStream::url('root/rmdirfail/sub'), 0700);
      $this->root->getChild('rmdirfail')->getChild('sub')
         ->chown(vfsStream::OWNER_ROOT)
         ->chgrp(vfsStream::GROUP_ROOT);
   }

   /**
    * @coversNothing
    */
   public function testPathTestConsts()
   {
      $test = Dir::READABLE;
      $expect = Path::EXISTS | Path::IS_DIR | Path::IS_READABLE;
      $this->assertEquals($test, $expect);

      $test = Dir::WRITABLE;
      $expect = Path::EXISTS | Path::IS_DIR | Path::IS_WRITABLE;
      $this->assertEquals($test, $expect);

      $test = Dir::READABLE_WRITABLE;
      $expect = Path::EXISTS | Path::IS_DIR | Path::IS_READABLE | Path::IS_WRITABLE;
   }

   /**
    * @covers \sndsgd\Dir::isReadable
    */
   public function testIsReadable()
   {
      $tests = [
         [vfsStream::url('root/test'), true],
         [vfsStream::url('root/noreadwrite'), false],
         [vfsStream::url('root/does/not/exist'), false],
      ];

      foreach ($tests as $test) {
         list($path, $isReadable) = $test;
         $result = (Dir::isReadable($path) === true);
         $this->assertEquals($isReadable, $result);
      }
   }

   /**
    * @covers \sndsgd\Dir::isWritable
    */
   public function testIsWritable()
   {
      $tests = [
         [vfsStream::url('root/test'), true],
         [vfsStream::url('root/noreadwrite'), false],
         [vfsStream::url('root/noreadwrite/does/not/exist'), false],
         [vfsStream::url('root/test/does/not/exist'), true],
      ];

      foreach ($tests as $test) {
         list($test, $expect) = $test;
         $result = (Dir::isWritable($test) === true);
         $this->assertEquals($expect, $result);
      }
   }

   /**
    * @covers \sndsgd\Dir::prepare
    */
   public function testPrepare()
   {
      $tests = [
         [vfsStream::url('root/test'), true],
         [vfsStream::url('root/noreadwrite'), false],
         [vfsStream::url('root/noreadwrite/does/not/exist'), false],
         [vfsStream::url('root/test/does/not/exist'), true],
      ];

      foreach ($tests as $test) {
         list($test, $expect) = $test;
         $result = (Dir::prepare($test) === true);
         $this->assertEquals($expect, $result);
      }
   }

   /**
    * @covers \sndsgd\Dir::sanitizeName
    */
   public function testSanitizeName()
   {
      $test = __METHOD__.'.test';
      $expect = 'DirTest__testSanitizeName_test';
      $this->assertEquals($expect, Dir::sanitizeName($test));

      $test = "!@#$%^&*(";
      $expect = "_________";
      $this->assertEquals($expect, Dir::sanitizeName($test));
   }

   /**
    * @covers \sndsgd\Dir::sanitizeName
    * @expectedException InvalidArgumentException
    */
   public function testSanitizeNameException()
   {
      Dir::sanitizeName(__DIR__);
   }

   /**
    * @covers \sndsgd\Dir::isEmpty
    */
   public function testIsEmpty()
   {
      $tests = [
         [vfsStream::url('root/test/emptydir'), true],
         [vfsStream::url('root/test'), false]
      ];

      foreach ($tests as $test) {
         list($test, $expect) = $test;
         $result = (Dir::isEmpty($test) === true);
         $this->assertEquals($expect, $result);
      }
   }

   /**
    * @covers \sndsgd\Dir::isEmpty
    * @expectedException InvalidArgumentException
    */
   public function testIsEmptyException()
   {
      Dir::isEmpty(123);
   }

   /**
    * @covers \sndsgd\Dir::isEmpty
    * @expectedException InvalidArgumentException
    */
   public function testIsEmptyNonDirException()
   {
      Dir::isEmpty(vfsStream::url('root/noreadwrite'));
   }

   /**
    * @covers \sndsgd\Dir::copy
    */
   public function testCopy()
   {
      $source = Path::normalize(__DIR__);
      $dest = vfsStream::url('root/noreadwrite');
      $this->assertTrue(is_string(Dir::copy($source, $dest)));

      $dest = vfsStream::url('root/test');
      $this->assertTrue(is_string(Dir::copy($source, $dest)));

      $dest = vfsStream::url('root/empty');
      $this->assertTrue(Dir::copy($source, $dest));
   }

   /**
    * @covers \sndsgd\Dir::remove
    */
   public function testRemove()
   {
      $dir = vfsStream::url('root/test');
      $this->assertTrue(Dir::remove($dir));

      $dir = vfsStream::url('root/noreadwrite');
      $this->assertTrue(is_string(Dir::remove($dir)));

      # sub directory cannot be read or written
      $dir = vfsStream::url('root/rmdirfail');
      $this->assertTrue(is_string(Dir::remove($dir)));

      # child file cannot be deleted
      $dir = vfsStream::url('root/remove-fail');
      $this->assertTrue(is_string(Dir::remove($dir)));
   }
}

