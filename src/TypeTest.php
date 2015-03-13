<?php

namespace sndsgd;

use \InvalidArgumentException;


class TypeTest
{
   /**
    * Ensure a value is a string or null
    * 
    * @param * $value The value to test
    * @param string $name The variable/argument name
    * @return string|null
    * @throws \InvalidArgumentException If the value is not a string or null
    */
   public static function nullableString($value, $name)
   {
      if (!is_string($value) && !is_null($value)) {
         throw new InvalidArgumentException(
            "invalid value provided for '$name'; expecting a string or null"
         );
      }
      return $value;
   }
}

