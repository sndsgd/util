<?php

use \org\bovigo\vfs\vfsStream;
use \sndsgd\util\File;
use \sndsgd\util\Path;
use \sndsgd\util\Str;
use \sndsgd\util\Temp;


class FileTest extends PHPUnit_Framework_TestCase
{
   public static $nonExistingFile;
   public static $testFilePath;
   public static $testFileSize;
   public static $testFileLines;

   /**
    * @coversNothing
    */
   public static function setUpBeforeClass()
   {
      $root = vfsStream::setup('root');
      vfsStream::create([
         'test' => [
            'file.txt' => 'contents...',
            'move-me.txt' => 'contents...',
            'emptydir' => [
            ]
         ],
         'noreadwrite' => 'contents...'
      ]);

      chmod(vfsStream::url('root/noreadwrite'), 0700);

      $root->getChild('noreadwrite')
         ->chown(vfsStream::OWNER_ROOT)
         ->chgrp(vfsStream::GROUP_ROOT);

      self::$nonExistingFile = vfsStream::url('root/does-not-exist');
      self::$testFilePath = vfsStream::url('root/test.txt');

      $fp = fopen(self::$testFilePath, 'w');
      $bytes = 0;
      $len = rand(100, 200);
      for ($i=0; $i<$len; $i++) {
         $bytes += fwrite($fp, Str::random(rand(5000, 10000))."\n");
      }
      fclose($fp);

      self::$testFileSize = $bytes;
      self::$testFileLines = $len;
   }

   /**
    * @coversNothing
    */
   public function testPathTestConsts()
   {
      $test = File::READABLE;
      $expect = Path::EXISTS | Path::IS_FILE | Path::IS_READABLE;
      $this->assertEquals($test, $expect);

      $test = File::WRITABLE;
      $expect = Path::EXISTS | Path::IS_FILE | Path::IS_WRITABLE;
      $this->assertEquals($test, $expect);

      $test = File::READABLE_WRITABLE;
      $expect = Path::EXISTS | Path::IS_FILE | Path::IS_READABLE | Path::IS_WRITABLE;
      $this->assertEquals($test, $expect);

      $test = File::EXECUTABLE;
      $expect = Path::EXISTS | Path::IS_FILE | Path::IS_EXECUTABLE;
      $this->assertEquals($test, $expect);
   }

   /**
    * @covers \sndsgd\util\File::isReadable
    */
   public function testIsReadable()
   {
      $tests = [
         [vfsStream::url('root/test/file.txt'), true],
         [vfsStream::url('root/test/emptydir/file.txt'), false],
         [vfsStream::url('root/noreadwrite'), false],
      ];

      foreach ($tests as list($test, $expect)) {
         $result = File::isReadable($test) === true;
         $this->assertEquals($expect, $result);
      }
   }

   /**
    * @covers \sndsgd\util\File::isWritable
    */
   public function testIsWritable()
   {
      $tests = [
         [vfsStream::url('root/test/file.txt'), true],
         [vfsStream::url('root/test/emptydir/file.txt'), true],
         [vfsStream::url('root/noreadwrite/file.txt'), false],
         [vfsStream::url('root/does-not-exist/file.txt'), false],
      ];

      foreach ($tests as list($test, $expect)) {
         $result = File::isWritable($test) === true;
         $this->assertEquals($expect, $result);
      }
   }

   /**
    * @covers \sndsgd\util\File::prepare
    */
   public function testPrepare()
   {
      $tests = [
         [vfsStream::url('root/test/file.txt'), true],
         [vfsStream::url('root/test/emptydir/file.txt'), true],
         [vfsStream::url('root/noreadwrite/file.txt'), false],
         [vfsStream::url('root/does-not-exist/file.txt'), true],
      ];

      foreach ($tests as list($test, $expect)) {
         $result = File::prepare($test) === true;
         $this->assertEquals($expect, $result);
      }
   }

   /**
    * @covers \sndsgd\util\File::splitName
    */
   public function testSplitName()
   {
      $tests = [
         'file.txt' => ['file', 'txt'],
         '.hidden' => ['.hidden', null],
         '/tmp/test.file' => ['test', 'file'],
         '../test/file.ext' => ['file', 'ext']
      ];
      foreach ($tests as $test => $expect) {
         $this->assertEquals($expect, File::splitName($test));
      }

      $this->assertEquals('ext', File::splitName('/test/path/file.ext')[1]);
      $this->assertNull(File::splitName('/test/path/file')[1]);
   }


   /**
    * @covers \sndsgd\util\File::rename
    */
   public function testRename()
   {
      $from = vfsStream::url('root/test/move-me.txt');
      $to = vfsStream::url('root/test/newdir/ive-been-moved.txt');
      file_put_contents($from, '123');

      # this should work
      $result = File::rename($from, $to, 0664, 0775);
      $this->assertTrue(is_dir(dirname($to)));
      $this->assertEquals('123', file_get_contents($to));

      # this shouldnt work (file has been moved)
      $result = File::rename($from, $to, 0664, 0775);
      $this->assertFalse($result === true);

      $from = $to;
      $to = vfsStream::url('root/noreadwrite/newfile.txt');
      $result = File::rename($from, $to, 0664, 0775);
      $this->assertFalse($result === true);

      $to = vfsStream::url('root/noreadwrite/newfile/some/dir.txt');
      $result = File::rename($from, $to, 0664, 0775);
      $this->assertFalse($result === true);
   }

   /**
    * @covers \sndsgd\util\File::formatSize
    */
   public function testFormatSize()
   {
      $tests = [
         ['572.0 MB', 599785472, 1],
         ['572.0 MB', '599785472', 1],
         ['1 MB', 1234567, 0],
         ['1.18 MB', 1234567, 2],
         ['1.1498 GB', 1234567890, 4],
      ];

      foreach ($tests as list($expect, $bytes, $precision)) {
         $result = File::formatSize($bytes, $precision);
         $this->assertEquals($expect, $result);
      }
   }

   /**
    * @covers \sndsgd\util\File::formatSize
    */
   public function testFormatSizeForPath()
   {
      $expect = File::formatSize(self::$testFileSize, 2);
      $this->assertEquals($expect, File::formatSize(self::$testFilePath, 2));
   }

   /**
    * @covers \sndsgd\util\File::formatSize
    * @expectedException InvalidArgumentException
    */
   public function testFormatSizeInvalidBytes()
   {
      File::formatSize([1,2,3]);
   }

   /**
    * @covers \sndsgd\util\File::formatSize
    * @expectedException InvalidArgumentException
    */
   public function testFormatSizeInvalidPath()
   {
      File::formatSize(self::$nonExistingFile);
   }

   /**
    * @covers \sndsgd\util\File::countLines
    */
   public function testCountLines()
   {
      $lines = File::countLines(self::$testFilePath);
      $this->assertEquals(self::$testFileLines, $lines);
   }

   /**
    * @covers \sndsgd\util\File::countLines
    * @expectedException Exception
    */
   public function testCountLinesInvalidPath()
   {
      File::countLines(self::$nonExistingFile);
   }
}

