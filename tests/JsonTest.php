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
      $data = [
         '1' => 1,
         '2' => 2
      ];

      $result = Json::encodeFile($path, $data, Json::HUMAN);
      $this->assertEquals($result, true);

      $json = file_get_contents($path);
      $json = json_decode($json, true);      
      $this->assertEquals($data, $json);

      $path = '/__this__/path/better/not/exist.json';
      $result = Json::encodeFile($path, $data);
      $expect = 
         "failed to write JSON file; ".
         "failed to create directory '/__this__/path/better/not'";
      $this->assertEquals($expect, $result);


   }
}

