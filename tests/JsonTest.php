<?php

use \org\bovigo\vfs\vfsStream;
use \sndsgd\util\Json;
use \sndsgd\util\Str;
use \sndsgd\util\Temp;


class JsonTest extends PHPUnit_Framework_TestCase
{
   protected $invalidJSON = '{"missingTrailingQuote: "some value"}';

   private function getVfsFilePath()
   {
      $root = vfsStream::setup('root');
      $file = vfsStream::newFile('test.json')->at($root);
      return vfsStream::url($file->path());
   }

   /**
    * @coversNothing
    */
   public function testConstants()
   {
      $expect = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;
      $this->assertEquals($expect, Json::HUMAN);
   }

   /**
    * @covers \sndsgd\util\Json::getError
    */
   public function testGetError()
   {
      $res = json_decode($this->invalidJSON, true);
      $error = Json::getError();
      $this->assertEquals($error, 'syntax error');
   }

   /**
    * @covers \sndsgd\util\Json::encodeFile
    */
   public function testEncodeFile()
   {
      # should work swimmingly
      $path = $this->getVfsFilePath();
      $test = ['one' => 1, 'two' => 2];
      $result = Json::encodeFile($path, $test, Json::HUMAN);
      $this->assertTrue($result);
      $json = file_get_contents($path);
      $json = json_decode($json, true);      
      $this->assertEquals($test, $json);

      # doesnt exist
      $path = vfsStream::url('this/path/does/not/exist/json');
      $result = Json::encodeFile($path, $test);
      $this->assertTrue(is_string($result));
   }

   /**
    * @covers \sndsgd\util\Json::encodeFile
    */
   public function testEncodeJsonEncodeError()
   {
      $path = $this->getVfsFilePath();

      # json_encode fails due to recursion limit
      $test = ['test'=>['test'=>['test'=>['test'=>['test'=>['hello']]]]]];
      $result = Json::encodeFile($path, $test, 0, 2);
      $this->assertTrue(is_string($result));
   }

   /**
    * @covers \sndsgd\util\Json::encodeFile
    */
   public function testEncodeFileFileWriteFailure()
   {
      $path = $this->getVfsFilePath();
      vfsStream::setQuota(10);
      $data = [ Str::random(1000) ];
      $this->assertTrue(is_string(Json::encodeFile($path, $data)));
   }
}

