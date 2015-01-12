<?php

namespace sndsgd;


/**
 * A base class for singletons
 *
 * @see http://www.phptherightway.com/pages/Design-Patterns.html#singleton
 */
class Singleton
{
   /**
    * Returns the *Singleton* instance of this class.
    *
    * @return object
    */
   public static function getInstance()
   {
      static $instance = null;
      if (null === $instance) {
         $instance = new static();
      }
      return $instance;
   }

   /**
    * Prevent the creation of a new instance outside the class
    */
   protected function __construct()
   {
   }

   /**
    * Prevent cloning of the singleton instance
    *
    * @return void
    */
   private function __clone()
   {
   }

   /**
    * Prevent unserializing of the singleton instance
    * 
    * @return void
    */
   private function __wakeup()
   {
   }
}

