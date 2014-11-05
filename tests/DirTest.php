<?php

use \org\bovigo\vfs\vfsStream;
use \sndsgd\util\Dir;
use \sndsgd\util\Path;
use \sndsgd\util\Temp;


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
         'noreadwrite' => []
      ]);

      chmod(vfsStream::url('root/noreadwrite'), 0700);

      $this->root->getChild('noreadwrite')
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
    * @covers \sndsgd\util\Dir::isReadable
    */
   public function testIsReadable()
   {
      $tests = [
         [vfsStream::url('root/test'), true],
         [vfsStream::url('root/noreadwrite'), false],
         [vfsStream::url('root/does/not/exist'), false],
      ];

      foreach ($tests as list($path, $isReadable)) {
         $result = (Dir::isReadable($path) === true);
         $this->assertEquals($isReadable, $result);
      }
   }

   /**
    * @covers \sndsgd\util\Dir::isWritable
    */
   public function testIsWritable()
   {
      $tests = [
         [vfsStream::url('root/test'), true],
         [vfsStream::url('root/noreadwrite'), false],
         [vfsStream::url('root/noreadwrite/does/not/exist'), false],
         [vfsStream::url('root/test/does/not/exist'), true],
      ];

      foreach ($tests as list($test, $expect)) {
         $result = (Dir::isWritable($test) === true);
         $this->assertEquals($expect, $result);
      }
   }

   /**
    * @covers \sndsgd\util\Dir::prepare
    */
   public function testPrepare()
   {
      $tests = [
         [vfsStream::url('root/test'), true],
         [vfsStream::url('root/noreadwrite'), false],
         [vfsStream::url('root/noreadwrite/does/not/exist'), false],
         [vfsStream::url('root/test/does/not/exist'), true],
      ];

      foreach ($tests as list($test, $expect)) {
         $result = (Dir::prepare($test) === true);
         $this->assertEquals($expect, $result);
      }
   }

   /**
    * @covers \sndsgd\util\Dir::isEmpty
    */
   public function testIsEmpty()
   {
      $tests = [
         [vfsStream::url('root/test/emptydir'), true],
         [vfsStream::url('root/test'), false]
      ];

      foreach ($tests as list($test, $expect)) {
         $result = (Dir::isEmpty($test) === true);
         $this->assertEquals($expect, $result);
      }
   }

   /**
    * @covers \sndsgd\util\Dir::isEmpty
    * @expectedException InvalidArgumentException
    */
   public function testIsEmptyException()
   {
      Dir::isEmpty(123);
   }

   /**
    * @covers \sndsgd\util\Dir::isEmpty
    * @expectedException InvalidArgumentException
    */
   public function testIsEmptyNonDirException()
   {
      Dir::isEmpty(vfsStream::url('root/noreadwrite'));
   }

   /**
    * @covers \sndsgd\util\Dir::remove
    */
   public function testRemove()
   {
      $tests = [
         [vfsStream::url('root/test'), true],
      ];

      foreach ($tests as list($test, $expect)) {
         $result = (Dir::remove($test) === true);
         $this->assertEquals($expect, $result);
      }
   }

   /**
    * @covers \sndsgd\util\Dir::remove
    */
   // public function testRemoveFailure()
   // {
   //    $tests = [
   //       [vfsStream::url('root/noreadwrite'), false]
   //    ];

   //    foreach ($tests as list($test, $expect)) {
   //       $result = (Dir::remove($test) === true);
   //       $this->assertEquals($expect, $result);
   //    }
   // }
}

