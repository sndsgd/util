<?php

namespace sndsgd;

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

   /**
    * Get a namespaced classname from a string of php
    * 
    * @param string $contents
    * @return string|null
    */
   public static function fromContents($contents)
   {
      $class = null;
      $namespace = null;
      $tokens = token_get_all($contents);
      for ($i=0, $len=count($tokens); $i<$len; $i++) {
         if ($tokens[$i][0] === T_NAMESPACE) {
            for ($j=$i+1, $jlen=count($tokens); $j<$jlen; $j++) {
               if ($tokens[$j][0] === T_STRING) {
                  $namespace .= '\\'.$tokens[$j][1];
               } 
               else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                  break;
               }
            }
         }
         else if ($tokens[$i][0] === T_CLASS) {
            for ($j=$i+1; $j<$len; $j++) {
               if ($tokens[$j] === '{') {
                  $class = $tokens[$i+2][1];
               }
            }
         }
      }

      if ($class === null) {
         return null;
      }
      return ($namespace === null)
         ? $class
         : trim($namespace.'\\'.$class, '\\');
   }
}

