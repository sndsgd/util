<?php

namespace sndsgd\util\data;

use \InvalidArgumentException;


/**
 * A trait for adding data storage to an object
 */
trait Manager
{
   /**
    * Data storage
    *
    * @var array.<string,mixed>
    */
   protected $data = [];

   /**
    * Overwrite the current data
    *
    * @param array.<string,mixed> $data The data to replace current data with
    * @return void
    */
   public function setData(array $data = [])
   {
      $this->data = $data;
   }

   /**
    * Add data
    *
    * @param string|array.<string,mixed> $key The name to stash data under
    * @param mixed $value Whatever needs to be stashed
    * @return void
    */
   public function addData($key, $value = null)
   {
      if (is_array($key)) {
         foreach ($key as $k => $v) {
            $this->data[$k] = $v;
         }
      }
      else if (is_string($key)) {
         $this->data[$key] = $value;   
      }
      else {
         throw new InvalidArgumentException(
            "invalid value provided for 'key'; expecting a key as a string ".
            "or an associative array"
         );
      }
   }

   /**
    * Remove data
    *
    * @param string $key The key of the data to remove
    * @return boolean Whether or not the data was removed
    */
   public function removeData($key = null)
   {
      if ($key === null) {
         $this->data = [];
         return true;
      }
      if (array_key_exists($key, $this->data)) {
         unset($this->data[$key]);
         return true;
      }
      return false;
   }

   /**
    * Retrieve data
    *
    * @param string $key The name to stash data under
    * @param mixed $value Whatever needs to be stashed
    * @return void
    */
   public function getData($key = null)
   {
      if ($key === null) {
         return $this->data;
      }
      else if (!array_key_exists($key, $this->data)) {
         return null;
      }
      return $this->data[$key];
   }
}

