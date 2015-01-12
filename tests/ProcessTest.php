<?php

namespace sndsgd;

use \org\bovigo\vfs\vfsStream;


class ProcessTest extends \PHPUnit_Framework_TestCase
{
   private function createInaccessibleFile()
   {
      $root = vfsStream::setup('root');
      vfsStream::newFile('test.txt', 0700)
         ->at($root)
         ->chgrp(vfsStream::GROUP_ROOT)
         ->chown(vfsStream::OWNER_ROOT);

      return vfsStream::url('root/test.txt');
   }

   public function testString()
   {
      $cmd = 'ls -l';
      $cwd = sys_get_temp_dir();
      $p = new Process($cmd, $cwd);
      $this->assertEquals(0, $p->exec());
      $this->assertTrue(is_string($p->getStdout()));
      $this->assertTrue(is_string($p->getStderr()));
      $this->assertEquals('', $p->getStderr());
   }

   public function testSetStdin()
   {
      $p = new Process('wc -w');
      $p->setStdin('one two three four');
      $p->exec();
      $result = trim($p->getStdout());
      $this->assertEquals(4, intval($result));
   }

   public function testSetStdinFile()
   {
      $tmpfile = tempnam(sys_get_temp_dir(), 'test-');
      file_put_contents($tmpfile, 'one two three four');

      $p = new Process('wc -w');
      $p->setStdinFile($tmpfile);
      $p->exec();
      $result = trim($p->getStdout());
      $this->assertEquals(4, intval($result));

      unlink($tmpfile);
   }

   /**
    * @expectedException InvalidArgumentException
    */
   public function testSetStdinFileException()
   {
      $p = new Process('pwd');
      $p->setStdinFile($this->createInaccessibleFile());
   }

   public function testArray()
   {
      $cmd = ['ls', '-l'];
      $cwd = sys_get_temp_dir();
      $p = new Process($cmd, $cwd);
      $this->assertEquals(0, $p->exec());
      $this->assertTrue(is_string($p->getStdout()));
      $this->assertTrue(is_string($p->getStderr()));
      $this->assertEquals('', $p->getStderr());
   }

   /**
    * @expectedException InvalidArgumentException
    */
   public function testSetOutputFileException()
   {
      $p = new Process('pwd');
      $p->setStdoutFile($this->createInaccessibleFile());
   }

   public function testSetStdoutFile()
   {
      $cwd = dirname(getcwd());
      $tmpfile = tempnam(sys_get_temp_dir(), 'test-');

      # test the stdout goes to the file
      $p = new Process('pwd', $cwd);
      $p->setStdoutFile($tmpfile);
      $exitcode = $p->exec();
      $contents = trim(file_get_contents($tmpfile));
      $this->assertEquals($cwd, $contents);

      # run the test again, this time appending the output
      $p = new Process('pwd', $cwd);
      $p->setStdoutFile($tmpfile, true);
      $exitcode = $p->exec();
      $contents = trim(file_get_contents($tmpfile));
      $this->assertEquals([$cwd, $cwd], explode(PHP_EOL, $contents));

      unlink($tmpfile);
   }

   public function testSetStderrFile()
   {
      $cmd = 'this-prgm-doesnt-exist';
      $tmpfile = tempnam(sys_get_temp_dir(), 'test-');

      # test the stderr goes to the file
      $p = new Process($cmd);
      $p->setStderrFile($tmpfile);
      $exitcode = $p->exec();
      $contents = trim(file_get_contents($tmpfile));
      $this->assertTrue(substr_count($contents, $cmd) === 1);

      # run the test again, this time appending the output
      $p = new Process($cmd);
      $p->setStderrFile($tmpfile, true);
      $exitcode = $p->exec();
      $contents = trim(file_get_contents($tmpfile));
      $this->assertTrue(substr_count($contents, $cmd) === 2);

      unlink($tmpfile);
   }

   public function testWorkingDirectory()
   {
      $cwd = dirname(getcwd());
      $p = new Process('pwd', $cwd);
      $p->exec();
      $this->assertEquals($cwd, trim($p->getStdout()));
   }

   public function testGetCommand()
   {
      $cmd = 'pwd';
      $p = new Process($cmd);
      $p->exec();
      $this->assertEquals($cmd, $p->getCommand());
   }

   public function testGetExitcode()
   {
      $cmd = 'pwd';
      $p = new Process($cmd);
      $exitcode = $p->exec();
      $this->assertEquals($exitcode, $p->getExitcode());
   }

   public function testExport()
   {
      $p = new Process('pwd');
      $p->exec();

      $data = $p->export();
      $this->assertEquals($data['command'], 'pwd');
   }
}
