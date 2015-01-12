<?php

use \SplFileInfo;
use \org\bovigo\vfs\vfsStream;
use \sndsgd\Dir;
use \sndsgd\Find;
use \sndsgd\Path;
use \sndsgd\Temp;


class FindTest extends PHPUnit_Framework_TestCase
{
   private static $dir;

   private static $structure = [
      'file.txt' => 'contents...',
      'empty-1' => [],
      'empty-2' => [],
      'not-empty' => [
         'file.txt' => 'contents...',
         'has-children' => [
            'file.txt' => 'contents...',
            'another-file.txt' => 'contents',
            'empty' => []
         ],
         'empty' => [],
      ]
   ];

   private static function createStructure($dir, $contents)
   {
      foreach ($contents as $name => $contents) {
         $path = $dir.DIRECTORY_SEPARATOR.$name;
         if (is_array($contents)) {
            mkdir($path, 0777, true);
            self::createStructure($path, $contents);
         }
         else {
            file_put_contents($path, $contents);
         }
      }
   }

   /**
    * Use this instead of Temp::dir to prevent the registration of the dir
    */
   private static function createTempDir()
   {
      $tmpdir = sys_get_temp_dir();
      $name = Dir::sanitizeName(__CLASS__);
      do {
         $rand = substr(md5(microtime(true)), 0, 6);
         $dir = $tmpdir.DIRECTORY_SEPARATOR.$name.$rand;
      }
      while (@mkdir($dir, 0775) === false);
      return $dir;
   }

   public static function setUpBeforeClass()
   {
      self::$dir = self::createTempDir();
      self::createStructure(self::$dir, self::$structure);
   }

   public static function tearDownAfterClass()
   {
      Dir::remove(self::$dir);
   }

   /**
    * @covers \sndsgd\Find::getIterator
    * @covers \sndsgd\Find::createIterator
    */
   public function testGetIterator()
   {
      $iterator = Find::getIterator(self::$dir);
      $this->assertInstanceOf('DirectoryIterator', $iterator);

      $iterator = Find::getIterator(self::$dir, Find::RECURSIVE);
      $this->assertInstanceOf('RecursiveIteratorIterator', $iterator);
   }

   /**
    * @covers \sndsgd\Find::getIterator
    * @expectedException InvalidArgumentException
    */
   public function testGetIteratorNonStringDir()
   {
      $iterator = Find::getIterator(42);
   }

   /**
    * @covers \sndsgd\Find::getIterator
    * @expectedException InvalidArgumentException
    */
   public function testGetIteratorNonReadableDir()
   {
      $root = vfsStream::setup('root');
      vfsStream::newDirectory('test', 0700)
         ->at($root)
         ->chgrp(vfsStream::GROUP_ROOT)
         ->chown(vfsStream::OWNER_ROOT);

      $dir = vfsStream::url('root/test');
      $iterator = Find::getIterator($dir);
   }

   /**
    * @covers \sndsgd\Find::getIterator
    * @expectedException InvalidArgumentException
    */
   public function testGetIteratorNonIntegerOptions()
   {
      $iterator = Find::getIterator(self::$dir, 'asd');
   }

   /**
    * @covers \sndsgd\Find::directories
    */
   public function testDirectories()
   {
      $results = Find::directories(self::$dir);
      $this->assertEquals(3, count($results));

      $results = Find::directories(self::$dir, Find::RECURSIVE);
      $this->assertEquals(6, count($results));
   }

   /**
    * @covers \sndsgd\Find::emptyDirectories
    */
   public function testEmptyDirectories()
   {
      $results = Find::emptyDirectories(self::$dir);
      $this->assertEquals(2, count($results));

      $results = Find::emptyDirectories(self::$dir, Find::RECURSIVE);
      $this->assertEquals(4, count($results));
   }

   /**
    * @covers \sndsgd\Find::filesByExtension
    */
   public function testFilesByExtension()
   {
      $results = Find::filesByExtension(self::$dir, 'txt');
      $this->assertEquals(1, count($results));
      
      $results = Find::filesByExtension(self::$dir, 'txt', Find::RECURSIVE);
      $this->assertEquals(4, count($results));
   }

   /**
    * @covers \sndsgd\Find::brokenLinks
    */
   public function testBrokenLinks()
   {
      # create broken symlinks in these directories
      $dirs = [
         self::$dir,
         self::$dir.DIRECTORY_SEPARATOR.'not-empty'
      ];

      foreach ($dirs as $dir) {
         $dir = $dir.DIRECTORY_SEPARATOR;
         $source = $dir.'source.file';
         $link = $dir.'link.file';
         file_put_contents($source, 'contents...');
         symlink($source, $link);
         unlink($source);
      }

      $results = Find::brokenLinks(self::$dir);
      $this->assertEquals(1, count($results));

      $results = Find::brokenLinks(self::$dir, Find::RECURSIVE);
      $this->assertEquals(2, count($results));
   }

}

