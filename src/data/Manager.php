<?php

namespace sndsgd\util\data;


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
    * Helpful when performing expensive operations during validation on data
    * that will be required later in the script
    * @param string $key The name to stash data under
    * @param mixed $value Whatever needs to be stashed
    * @return void
    */
   public function addData($key, $value)
   {
      $this->data[$key] = $value;
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

