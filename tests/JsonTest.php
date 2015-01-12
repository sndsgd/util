<?php

use \org\bovigo\vfs\vfsStream;
use \sndsgd\Json;
use \sndsgd\Str;
use \sndsgd\Temp;

/**
 * @coversDefaultClass sndsgd\Json
 */
class JsonTest extends PHPUnit_Framework_TestCase
{
   /**
    * @coversNothing
    */
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
      $expect = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
      $this->assertEquals($expect, Json::HUMAN);
   }

   /**
    * @covers ::getError
    */
   public function testGetError()
   {
      $invalidJSON = '{"missingTrailingQuote: "some value"}';
      $res = json_decode($invalidJSON, true);
      $error = Json::getError();
      $this->assertEquals($error, 'syntax error');
   }

   /**
    * @covers ::encodeFile
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
    * @covers ::encodeFile
    */
   public function testEncodeFileFileWriteFailure()
   {
      $path = $this->getVfsFilePath();
      vfsStream::setQuota(10);
      $data = [ Str::random(1000) ];
      $this->assertTrue(is_string(Json::encodeFile($path, $data)));
   }

   /**
    * @covers ::encodeFile
    */
   private function writeTestJsonFile()
   {
      $data = [
         'one' => 1,
         'two' => 2,
         'three' => [1,2,3]
      ];

      $path = $this->getVfsFilePath();
      Json::encodeFile($path, $data);
      return [$path, $data];
   }

   /**
    * @covers ::decodeFile
    */
   public function testDecodeFileNotReadable()
   {
      $path = vfsStream::url('this/path/does/not/exist/json');
      $result = Json::decodeFile($path, true);
      $this->assertTrue(is_string($result));
   }

   /**
    * @covers ::decodeFile
    */
   public function testDecodeFileDecodeFailure()
   {
      $path = $this->getVfsFilePath();
      file_put_contents($path, '{');
      $result = Json::decodeFile($path, true);
      $this->assertTrue(is_string($result));
   }

   /**
    * @covers ::decodeFile
    * @expectedException Exception
    */
   public function testDecodeFileBadTypeException()
   {
      $path = $this->getVfsFilePath();
      file_put_contents($path, '"testing... 1,2,3"');
      $result = Json::decodeFile($path, true);
   }

   /**
    * @covers ::decodeFile
    */
   public function testDecodeFileSuccess()
   {
      list($path, $data) = $this->writeTestJsonFile();
      $result = Json::decodeFile($path, true);
      $this->assertEquals($data, $result);
   }
}

