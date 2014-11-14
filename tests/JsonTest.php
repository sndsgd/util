<?php

use \sndsgd\util\Json;
use \sndsgd\util\Temp;


class JsonTest extends PHPUnit_Framework_TestCase
{
   protected $invalidJSON = '{"missingTrailingQuote: "some value"}';

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
      $path = sys_get_temp_dir().'/test-json.json';
      
      # json_encode fails due to recursion limit
      $test = ['test'=>['test'=>['test'=>['test'=>['test'=>['hello']]]]]];
      $result = Json::encodeFile($path, $test, 0, 2);
      $this->assertTrue(is_string($result));

      $test = ['one' => 1, 'two' => 2];
      $result = Json::encodeFile($path, $test, Json::HUMAN);
      $this->assertTrue($result);
      $json = file_get_contents($path);
      $json = json_decode($json, true);      
      $this->assertEquals($test, $json);

      $path = '/__this__/path/better/not/exist.json';
      $result = Json::encodeFile($path, $test);
      $this->assertTrue(is_string($result));
   }
}

