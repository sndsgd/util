<?php

use \org\bovigo\vfs\vfsStream;
use \sndsgd\File;
use \sndsgd\Path;
use \sndsgd\Str;


/**
 * @coversDefaultClass \sndsgd\File
 */
class FileTest extends PHPUnit_Framework_TestCase
{
   protected function setUp()
   {
      $this->root = vfsStream::setup('root');
      vfsStream::create([
         'test' => [
            'file.txt' => 'contents...',
            'move-me.txt' => 'contents...',
            'emptydir' => [
            ]
         ],
         'noreadwrite' => 'contents...'
      ]);

      $this->root->getChild('noreadwrite')
         ->chmod(0700)
         ->chgrp(vfsStream::GROUP_ROOT)
         ->chown(vfsStream::OWNER_ROOT);
   }

   protected function createTestFile()
   {
      $path = vfsStream::url('root/test.txt');
      $fp = fopen($path, 'w');
      $bytes = 0;
      $len = rand(100, 200);
      for ($i=0; $i<$len; $i++) {
         $bytes += fwrite($fp, Str::random(rand(5000, 10000))."\n");
      }
      fclose($fp);
      return [$path, $bytes, $len];
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
    * @covers ::isReadable
    */
   public function testIsReadable()
   {
      $path = vfsStream::url('root/test/file.txt');
      $this->assertTrue(File::isReadable($path));

      $path = vfsStream::url('root/test/emptydir/file.txt');
      $this->assertTrue(is_string(File::isReadable($path)));

      $path = vfsStream::url('root/noreadwrite');
      $this->assertTrue(is_string(File::isReadable($path)));
   }

   /**
    * @covers ::isWritable
    */
   public function testIsWritable()
   {
      $tests = [
         [vfsStream::url('root/test/file.txt'), true],
         [vfsStream::url('root/test/emptydir/file.txt'), true],
         [vfsStream::url('root/noreadwrite/file.txt'), false],
         [vfsStream::url('root/does-not-exist/file.txt'), true]
      ];

      foreach ($tests as $test) {
         list($test, $expect) = $test;
         $result = File::isWritable($test) === true;
         $this->assertEquals($expect, $result);
      }
   }

   /**
    * @covers ::prepare
    */
   public function testPrepare()
   {
      $tests = [
         [vfsStream::url('root/test/file.txt'), true],
         [vfsStream::url('root/test/emptydir/file.txt'), true],
         [vfsStream::url('root/noreadwrite/file.txt'), false],
         [vfsStream::url('root/does-not-exist/file.txt'), true],
      ];

      foreach ($tests as $test) {
         list($test, $expect) = $test;
         $result = File::prepare($test) === true;
         $this->assertEquals($expect, $result);
      }
   }

   /**
    * @covers ::write
    */
   public function testWrite()
   {
      # success
      $path = vfsStream::url('root/test/a-new-dir/file.txt');
      $contents = 'test';
      $this->assertTrue(File::write($path, $contents, 0));

      # prepare failure
      $path = vfsStream::url('root/noreadwrite/new-file.txt');
      $contents = 'test';
      $this->assertFalse(File::write($path, $contents, 0) === true);
   }

   /**
    * @covers ::sanitizeName
    */
   public function testSanitizeName()
   {
      $test = __METHOD__.'.test';
      $expect = 'FileTest__testSanitizeName.test';
      $this->assertEquals($expect, File::sanitizeName($test));

      $test = "!@#$%^&*(";
      $expect = "_________";
      $this->assertEquals($expect, File::sanitizeName($test));
   }

   /**
    * @covers ::sanitizeName
    * @expectedException InvalidArgumentException
    */
   public function testSanitizeNameException()
   {
      File::sanitizeName(__FILE__);
   }

   /**
    * @covers ::splitName
    */
   public function testSplitNameFile()
   {
      # with an extension
      list($name, $ext) = File::splitName('file.txt');
      $this->assertEquals('file', $name);
      $this->assertEquals('txt', $ext);
      # without extension
      list($name, $ext) = File::splitName('file');
      $this->assertEquals('file', $name);
      $this->assertNull($ext);
      # hidden file with extension
      list($name, $ext) = File::splitName('.hidden.txt');
      $this->assertEquals('.hidden', $name);
      $this->assertEquals('txt', $ext);
      # hidden file no extension
      list($name, $ext) = File::splitName('.hidden');
      $this->assertEquals('.hidden', $name);
      $this->assertNull($ext);
   }

   /**
    * @covers ::splitName
    */
   public function testSplitNameRootChild()
   {
      # with an extension
      list($name, $ext) = File::splitName('/file.txt');
      $this->assertEquals('file', $name);
      $this->assertEquals('txt', $ext);
      # without extension
      list($name, $ext) = File::splitName('/file');
      $this->assertEquals('file', $name);
      $this->assertNull($ext);
      # hidden file with extension
      list($name, $ext) = File::splitName('/.hidden.txt');
      $this->assertEquals('.hidden', $name);
      $this->assertEquals('txt', $ext);
      # hidden file no extension
      list($name, $ext) = File::splitName('/.hidden');
      $this->assertEquals('.hidden', $name);
      $this->assertNull($ext);
   }

   /**
    * @covers ::splitName
    */
   public function testSplitNameAbsolutePath()
   {
      # with an extension
      list($name, $ext) = File::splitName('/some/path/file.txt');
      $this->assertEquals('file', $name);
      $this->assertEquals('txt', $ext);
      # without extension
      list($name, $ext) = File::splitName('/some/path/file');
      $this->assertEquals('file', $name);
      $this->assertNull($ext);
      # hidden file with extension
      list($name, $ext) = File::splitName('/some/path/.hidden.txt');
      $this->assertEquals('.hidden', $name);
      $this->assertEquals('txt', $ext);
      # hidden file no extension
      list($name, $ext) = File::splitName('/some/path/.hidden');
      $this->assertEquals('.hidden', $name);
      $this->assertNull($ext);
   }

   /**
    * @covers ::splitName
    */
   public function testSplitNameRelativePath()
   {
      # with an extension
      list($name, $ext) = File::splitName('../parent/dir/file.txt');
      $this->assertEquals('file', $name);
      $this->assertEquals('txt', $ext);
      # without extension
      list($name, $ext) = File::splitName('../parent/dir/file');
      $this->assertEquals('file', $name);
      $this->assertNull($ext);
      # hidden file with extension
      list($name, $ext) = File::splitName('../parent/dir/.hidden.txt');
      $this->assertEquals('.hidden', $name);
      $this->assertEquals('txt', $ext);
      # hidden file no extension
      list($name, $ext) = File::splitName('../parent/dir/.hidden');
      $this->assertEquals('.hidden', $name);
      $this->assertNull($ext);
   }

   /**
    * @covers ::splitName
    */
   public function testSplitNameMissingExtensionValue()
   {
      # provide an empty string as the default value for a missing extension
      list($name, $ext) = File::splitName('filename', '');
      $this->assertEquals('filename', $name);
      $this->assertTrue($ext === '');
   }   


   /**
    * @covers ::rename
    */
   public function testRename()
   {
      $content = Str::random(1000);
      $from = vfsStream::url('root/move-me.txt');
      file_put_contents($from, $content);
      $to = vfsStream::url('root/ive-been-moved.txt');

      # this should work
      $result = File::rename($from, $to, 0664, 0775);
      $this->assertTrue(is_dir(dirname($to)));
      $this->assertEquals($content, file_get_contents($to));

      # this shouldnt work (file has been moved)
      $result = File::rename($from, $to, 0664, 0775);
      $this->assertFalse($result === true);

      $from = $to;
      $to = vfsStream::url('root/noreadwrite/newfile.txt');
      $result = File::rename($from, $to, 0664, 0775);
      $this->assertTrue(is_string($result));

      $to = vfsStream::url('root/noreadwrite/newfile/some/dir.txt');
      $result = File::rename($from, $to, 0664, 0775);
      $this->assertFalse($result === true);
   }

   /**
    * @covers ::rename
    */
   public function testRenameWriteFail()
   {
      # create a file to copy to the vfs, and set a quota to prevent it
      $source = tempnam(sys_get_temp_dir(), 'test-file-');
      file_put_contents($source, Str::random(1000));
      $dest = vfsStream::url('root/dest.txt');
      vfsStream::setQuota(1);
      $result = File::rename($source, $dest);
      $this->assertTrue(is_string($result));

      foreach ([$source, $dest] as $path) {
         if (file_exists($path)) {
            unlink($path);
         }
      }
   }

   /**
    * @covers ::formatSize
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

      foreach ($tests as $test) {
         list($expect, $bytes, $precision) = $test;
         $result = File::formatSize($bytes, $precision);
         $this->assertEquals($expect, $result);
      }
   }

   /**
    * @covers ::formatSize
    */
   public function testFormatSizeForPath()
   {
      list($path, $bytes, $lines) = $this->createTestFile();
      $expect = File::formatSize($bytes, 2);
      $this->assertEquals($expect, File::formatSize($path, 2));
   }

   /**
    * @covers ::formatSize
    * @expectedException InvalidArgumentException
    */
   public function testFormatSizeInvalidBytes()
   {
      File::formatSize([1,2,3]);
   }

   /**
    * @covers ::formatSize
    * @expectedException InvalidArgumentException
    */
   public function testFormatSizeInvalidPath()
   {
      File::formatSize(vfsStream::url('root/to/non/existing/file.txt'));
   }

   /**
    * @covers ::countLines
    */
   public function testCountLines()
   {
      list($path, $bytes, $lines) = $this->createTestFile();
      $this->assertEquals($lines, File::countLines($path));
   }
}

