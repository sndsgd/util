<?php

namespace sndsgd;

use \SplPriorityQueue;


/**
 * A priority queue with logical priority handling
 * 
 * @see http://mwop.net/blog/253-Taming-SplPriorityQueue.html
 */
class PriorityQueue extends SplPriorityQueue
{
   /**
    * A counter to help keep priorities in order
    * 
    * @var integer
    */
   protected $counter = PHP_INT_MAX;

   /**
    * Insert a value into the queue
    * 
    * @param mixed $value The value to insert
    * @param mixed $priority The priority to give the value
    */
   public function insert($value, $priority)
   {
      if (is_int($priority)) {
         $priority = [$priority, $this->counter--];
      }
      parent::insert($value, $priority);
   }
}

