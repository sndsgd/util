<?php

use \org\bovigo\vfs\vfsStream;
use \org\bovigo\vfs\vfsStreamDirectory;
use \org\bovigo\vfs\vfsStreamFile;
use \sndsgd\Path;


class PathTest extends PHPUnit_Framework_TestCase
{
   /**
    * @covers \sndsgd\Path::test
    */
   public function testTest()
   {
      $e = Path::EXISTS;
      $f = Path::IS_FILE;
      $d = Path::IS_DIR;
      $r = Path::IS_READABLE;
      $w = Path::IS_WRITABLE;
      $x = Path::IS_EXECUTABLE;

      $root = vfsStream::setup('root');
      (new vfsStreamDirectory('dir-all', 0777))->at($root);
      (new vfsStreamDirectory('dir-read', 0400))->at($root);
      (new vfsStreamDirectory('dir-write', 0200))->at($root);
      (new vfsStreamDirectory('dir-none', 0000))->at($root);
      (new vfsStreamFile('file-all', 0777))->at($root);
      (new vfsStreamFile('file-read', 0400))->at($root);
      (new vfsStreamFile('file-write', 0200))->at($root);
      (new vfsStreamFile('file-exec', 0100))->at($root);
      (new vfsStreamFile('file-none', 0000))->at($root);

      $tests = [
         ['root/dir-all', $e | $d | $r | $w, true],
         ['root/dir-read', $e | $d | $r, true],
         ['root/dir-read', $e | $d | $w, false],
         ['root/dir-write', $e | $d | $w, true],
         ['root/dir-write', $e | $d | $r, false],
         ['root/dir-none', $d | $r, false],
         ['root/dir-none', $d | $w, false],
         ['root/dir-none', $d | $r | $w, false],
         ['root/dir-doesnt-exist', $e | $d | $r | $w, false],
         ['root/dir-all', $f, false],

         ['root/file-all', $e | $f | $r | $w | $x, true],
         ['root/file-read', $e | $f | $r, true],
         ['root/file-read', $e | $f | $w, false],
         ['root/file-write', $e | $f | $w, true],
         ['root/file-write', $e | $f | $r, false],
         ['root/file-exec', $e | $f | $x, true],
         ['root/file-none', $f, true],
         ['root/file-none', $f | $r, false],
         ['root/file-none', $f | $w, false],
         ['root/file-none', $f | $r | $w, false],
         ['root/file-none', $x, false],
         ['root/file-all', $d, false],
      ];

      foreach ($tests as $test) {
         list($path, $flags, $expect) = $test;
         $result = (Path::test(vfsStream::url($path), $flags) === true);
         $this->assertEquals($expect, $result);
      }
   }

   /**
    * @covers \sndsgd\Path::test
    * @expectedException InvalidArgumentException
    */
   public function testTestException()
   {
      Path::test(123, Path::EXISTS);
   }

   /**
    * @covers \sndsgd\Path::normalize
    */
   public function testNormalize()
   {
      $cwd = getcwd();
      $dir = dirname($cwd);

      $tests = [
         '/tmp///path/.//file.txt' => '/tmp/path/file.txt',
         '/tmp/path/../file.txt' => '/tmp/file.txt',
         '/tmp/test/path/../../file.txt' => '/tmp/file.txt',
         'test/./path/one/..' => 'test/path',
         '.' => $cwd,
         './' => $cwd,
         "./test" => "$cwd/test",
         "../test" => "$dir/test",
         '..' => $dir,
         '../' => $dir
      ];

      foreach ($tests as $test => $expect) {
         $this->assertEquals($expect, Path::normalize($test));
      }
   }

   /**
    * @covers \sndsgd\Path::relative
    */
   public function testRelative()
   {
      $from = '/one/two/file.txt';
      $to = '/one/file.txt';
      $expect = '../file.txt';
      $this->assertEquals($expect, Path::relative($from, $to));

      $from = '/a/b/c';
      $to = '/x/y';
      $expect = '../../x/y';
      $this->assertEquals($expect, Path::relative($from, $to));
   }
}

