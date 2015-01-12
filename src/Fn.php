<?php

namespace sndsgd;


/**
 * Function and method utility methods
 */
class Fn
{
   /**
    * Verify a callable provided as a string
    *
    * @param string $fn The callable to verify
    * @return string|null
    * @return string The method or function WAS found
    * @return null No method or function was found
    */
   public static function exists($fn)
   {
      if (strpos($fn, '::') !== false) {
         list($class, $method) = explode('::', $fn);
         return (method_exists($class, $method)) ? $fn : null;
      }
      else if (!function_exists($fn)) {
         return null;
      }
      return $fn;
   }
}

