<?php

namespace sndsgd\util;

use \InvalidArgumentException;


/**
 * Classname utility methods
 */
class Classname
{
   // regex used to trim excess junk off possible classname strings
   const REGEX_TRIM = '/(^[^a-z0-9_]+)|([^a-z0-9_]+$)/i';
   // regex used to split strings into namespace and classname parts
   const REGEX_SPLIT = '/([^a-z0-9_])+/i';

   /**
    * Split a string into namespace and classname sections
    * 
    * @param string $class
    * @return array.<string>
    */
   public static function split($class)
   {
      if (!is_string($class)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'class'; expecting a string"
         );
      }
      $class = preg_replace(self::REGEX_TRIM, '', $class);
      return preg_split(self::REGEX_SPLIT, $class);
   }

   /**
    * Convert a string into a namespaced classname
    * 
    * @param string|array.<string> $class
    * @return string
    */
   public static function toString($class, $separator = '\\')
   {
      if (is_string($class)) {
         $class = self::split($class);   
      }
      return implode($separator, $class);
   }

   /**
    * Convert a string into a namespaced method name
    * 
    * @param string|array.<string> $class
    * @param boolean $asArray Whether or not to return as an array
    * @return string|array.<string>
    */
   public static function toMethod($class, $asArray = false)
   {
      if (is_string($class)) {
         $class = self::split($class);
      }
      $method = array_pop($class);
      $classname = implode('\\', $class);
      return ($asArray) ? [$classname, $method] : "$classname::$method";
   }
}

