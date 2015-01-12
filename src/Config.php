<?php

namespace sndsgd;

use \Exception;


/**
 * Config storage and retrieval
 */
class Config
{
   /**
    * Config value storage
    * 
    * @var array.<string,mixed>
    */
   private static $values = [];

   /**
    * Set initial config values
    * 
    * @param array.<string,mixed> $values
    */
   public static function init(array $values = [])
   {
      self::$values = $values;
   }

   /**
    * Set one or more config values
    * 
    * @param string|array $key The unique identifier for the value
    * @param number|string|null $value The value
    */
   public static function set($key, $value = null)
   {
      if (is_array($key)) {
         self::$values = array_merge(self::$values, $key);
      }
      else {
         self::$values[$key] = $value;   
      }
   }

   /**
    * Get a config value
    * 
    * @param string $key The unique key for the value to fetch
    * @param string|null $default A value to return if the key does not exist
    * @return mixed
    */
   public static function get($key, $default = null)
   {
      return (array_key_exists($key, self::$values))
         ? self::$values[$key]
         : $default;
   }

   /**
    * Get a config value, and throw an exception if it doesn't exist
    * 
    * @param string $key The unique key for the value to fetch
    * @return mixed
    * @throws Exception if $key is not set
    */
   public static function getRequired($key)
   {
      if (array_key_exists($key, self::$values)) {
         return self::$values[$key];
      }
      throw new Exception("the required config value '$key' was not found");
   }

   /**
    * Get multiple values with specific keys as array
    * 
    * @param array.<string,string> $map configName => returnName
    * @return array.<string,mixed> All values are found
    * @return string An error message indicating a missing config value
    */
   public static function getAs(array $map)
   {
      $ret = [];
      foreach ($map as $key => $retKey) {
         if (!array_key_exists($key, self::$values)) {
            return "the required config value '$key' was not found";
         }
         $ret[$retKey] = self::$values[$key];
      }
      return $ret;
   }

   /**
    * Get the entire config array
    * 
    * @return array.<string,mixed>
    */
   public static function getAll()
   {
      return self::$values;
   }
}

