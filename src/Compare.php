<?php

namespace sndsgd;


/**
 * Comparison utility methods
 */
class Compare
{
   /**
    * Determine if values are equal
    * 
    * @param mixed $a
    * @param mixed $b
    * @return boolean
    */
   public static function equal($a, $b)
   {
      return ($a == $b);
   }

   /**
    * Determine if values are identical
    * 
    * @param mixed $a
    * @param mixed $b
    * @return boolean
    */
   public static function strictEqual($a, $b)
   {
      return ($a === $b);
   }
}

