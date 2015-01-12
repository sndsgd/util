<?php

namespace sndsgd;

use \Exception;
use \sndsgd\File;


/**
 * JSON utility methods
 */
class Json
{
   // JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
   const HUMAN = 448;

   /**
    * JSON error numbers and their relevant messages
    * 
    * @var array.<integer,string>
    */
   private static $errors = [
      JSON_ERROR_NONE => 'no error occured',
      JSON_ERROR_DEPTH => 'maximim data nesting depth has been exceeded',
      JSON_ERROR_STATE_MISMATCH => 'invalid or malformed JSON',
      JSON_ERROR_CTRL_CHAR => 'control character error (check encoding)',
      JSON_ERROR_SYNTAX => 'syntax error',
      JSON_ERROR_UTF8 => 'malformed UTF-8 characters (check encoding)'
   ];

   /**
    * Get an error message when a JSON encode/decode fails
    * 
    * @return string
    */
   public static function getError()
   {
      $error = json_last_error();
      return (isset(self::$errors[$error]))
         ? self::$errors[$error] 
         : 'unknown error occured';
   }

   /**
    * Encode a variable as JSON and write it to a file
    * 
    * @param string $path An absolute path to the file to write
    * @param mixed $data The variable to encode
    * @param bitmask $options json_encode options
    * @return boolean|string
    * @return boolean:true The variable was encoded and written successfully
    * @return string An error message describing the failure
    */
   public static function encodeFile(
      $path,
      $data,
      $options = 0
   ) {
      if (($test = File::prepare($path)) !== true) {
         $err = $test;
      }
      else if (($json = json_encode($data, $options)) === false) {
         $err = self::getError();
      }
      else if (@file_put_contents($path, $json) === false) {
         $err = "write operation failed on '$path'";
      }
      else {
         return true;   
      }
      return "failed to encode JSON file; $err";
   }

   /**
    * Attempt to decode a file that contains JSON
    *
    * Note: only use this method on file that contains JSON arrays or objects;
    * any other data type will result in an exception being thrown
    *
    * @param string $path The Absolute path to the json file to decode
    * @param boolean $assoc Whether or not to return an associative array
    * @param integer $opts Options to pass to json_decode
    * @param integer $depth The max recursion depth to parse
    * @return object|array|string
    * @return object|array The read and decode were successful
    * @return string An error message describing the failure
    * @throws Exception If the resulting data is not an object or array
    */
   public static function decodeFile(
      $path, 
      $assoc = false, 
      $opts = 0,
      $depth = 512
   )
   {
      if (($test = File::isReadable($path)) !== true) {
         $err = $test;
      }
      else if (($json = @file_get_contents($path)) === false) {
         $err = "read operation failed on '$path'";
      }
      else if (
         ($ret = json_decode($json, $assoc, $depth, $opts)) === null &&
         json_last_error() !== JSON_ERROR_NONE
      ) {
         $err = "decode operation failed on '$path'";
      }
      else if (!is_array($ret) && !is_object($ret)) {
         throw new Exception(
            "Invalid JSON value type in '$path'; ".
            "only use sndsgd\\Json::decodeFile() on files that ".
            "contain an array or an object"
         );
      }
      else {
         return $ret;
      }
      return "failed to decode JSON file; $err";
   }
}

