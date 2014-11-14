<?php

namespace sndsgd\util;

use \Exception;
use \sndsgd\util\File;
use \sndsgd\util\Path;


/**
 * JSON utility methods
 */
class Json
{
   // JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
   const HUMAN = 192;

   /**
    * JSON error numbers and their relevant messages
    * 
    * @var array.<integer => string>
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
    * @param integer $depth The maxmium depth
    * @return boolean|string
    * @return boolean:true The variable was encoded and written successfully
    * @return string An error message describing the failure
    */
   public static function encodeFile(
      $path,
      $data,
      $options = 0,
      $depth = 512
   ) {
      $test = File::prepare($path);
      if ($test !== true) {
         return "failed to write JSON file; $test";
      }

      $json = json_encode($data, $options, $depth);
      if ($json === false) {
         return "failed to encode JSON; ".self::getError();
      }
      else if (@file_put_contents($path, $json) === false) {
         return "failed to write JSON file; file write operation failed";
      }
      return true;
   }
}

