<?php

namespace sndsgd;

use \InvalidArgumentException;


/**
 * String utility methods
 */
class Str
{
   /**
    * Determine if a string starts with another
    * 
    * @param string $haystack
    * @param string $needle
    * @param boolean $caseSensitive
    * @return boolean
    */
   public static function startsWith($haystack, $needle, $caseSensitive = false)
   {
      return ($caseSensitive === false)
         ? strncasecmp($haystack, $needle, strlen($needle)) === 0
         : strncmp($haystack, $needle, strlen($needle)) === 0;
   }

   /**
    * Determine if a string ends with another
    * 
    * @param string $haystack
    * @param string $needle
    * @param boolean $caseSensitive
    * @return boolean
    */
   public static function endsWith($haystack, $needle, $caseSensitive = false)
   {
      $test = substr($haystack, -strlen($needle));
      return ($caseSensitive === false)
         ? strcasecmp($test, $needle) === 0
         : strcmp($test, $needle) === 0;
   }

   /**
    * Get a random string
    * 
    * @param integer $length The length of the resulting string
    * @return string
    */
   public static function random($length)
   {
      if (!is_int($length)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'length'; expecting an integer"
         );
      }
      $chars = base64_encode(openssl_random_pseudo_bytes($length * 2));
      $chars = preg_replace('/[^A-Za-z0-9]/', '', $chars);
      return substr($chars, 0, $length);
   }

   /**
    * Convert a string to a number
    * 
    * @param string $str
    * @return integer|float
    */
   public static function toNumber($str)
   {
      if (is_string($str)) {
         $str = preg_replace('/[^0-9-.]/', '', $str);
      }
      return (strpos($str, '.') === false) 
         ? intval($str) 
         : floatval($str);
   }

   /**
    * Convert boolean strings to real booleans
    * 
    * @param string|number $str
    * @return boolean|null
    */
   public static function toBoolean($str)
   {
      if (!is_string($str)) {
         $str = strval($str);
      }

      $str = strtolower($str);
      $values = [
         'true' => true,
         'false' => false,
         '1' => true,
         '0' => false,
         'on' => true,
         'off' => false,
         '' => false
      ];
      return (array_key_exists($str, $values)) 
         ? $values[$str] 
         : null;
   }

   /**
    * Convert a string to camelCase
    * 
    * @param string $input
    * @return string
    */
   public static function toCamelCase($input)
   {
      $input = trim($input);
      $fn = function($arg) {
         list($match, $char) = $arg;
         $ret = str_replace($char, "", $match);
         return strtoupper($ret);
      };
      return preg_replace_callback('/( |_|-){1,}[A-Za-z]/', $fn, $input);
   }

   /**
    * Convert a string to snake_case
    * 
    * @param string $input
    * @param boolean $uppercase
    * @return string
    */
   public static function toSnakeCase($input, $uppercase = false)
   {
      $input = trim($input);
      $input = preg_replace('/[^a-z0-9]+/i', '_', $input);
      $fn = function($arg) {
         list($match, $char) = $arg;
         return $match[0].'_'.$match[1];
      };
      $ret = preg_replace_callback('/([a-z])[A-Z]/', $fn, $input);
      return ($uppercase) 
         ? strtoupper($ret) 
         : strtolower($ret);
   }

   /**
    * Remove all tabs that occur immediately after a newline
    * 
    * @param string $str
    * @return string
    */
   public static function stripPostNewlineTabs($str)
   {
      $regex = '/'.PHP_EOL.'[\t]+/';
      return preg_replace($regex, PHP_EOL, $str);
   }

   /**
    * Remove empty lines
    * 
    * @param string $str
    * @return string
    */
   public static function stripEmptyLines($str)
   {
      $regex = "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/";
      return preg_replace($regex, PHP_EOL, $str);
   }
}

