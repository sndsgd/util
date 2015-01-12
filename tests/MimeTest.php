<?php

use \sndsgd\Mime;


class MimeTest extends PHPUnit_Framework_TestCase
{
   public function testGetTypeFromExtension()
   {
      $tests = [
         'aiff' => 'audio/x-aiff',
         'atom' => 'application/atom+xml',
         'c' => 'text/x-csrc',
         'css' => 'text/css',
         'csv' => 'text/csv',
         'jpeg' => 'image/jpeg',
         'jpg' => 'image/jpeg',
         'js' => 'application/javascript',
         'json' => 'application/json',
         'mid' => 'audio/midi',
         'ogg' => 'audio/ogg',
         'txt' => 'text/plain',
         'zip' => 'application/zip',
         'asdasdasdasdasdasd' => 'application/octet-stream'
      ];

      foreach ($tests as $test => $expect) {
         $this->assertEquals($expect, Mime::getTypeFromExtension($test));
      }
   }

   public function testGetExtension()
   {
      $fn = 'sndsgd\\Mime::getExtension';
      $tests = [
         'gif' => 'image/gif',
         'jpg' => 'image/jpeg',
         'js' => 'application/javascript',
         'js' => 'application/x-javascript',
         'css' => 'text/css',
         'unknown' => ['doesnt/exist', 'unknown']
      ];
      foreach ($tests as $expect => $test) {
         $result = is_array($test)
            ? call_user_func_array($fn, $test)
            : call_user_func($fn, $test);
         $this->assertEquals($expect, $result);
      }
   }
}

