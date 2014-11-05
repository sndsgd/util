<?php

use \sndsgd\util\PriorityQueue;


class PriorityQueueTest extends PHPUnit_Framework_TestCase
{
   public function testInsert()
   {
      $values = [
         ['three', 1],
         ['two', 50],
         ['four', 1],
         ['one', 100]
      ];

      $pq = new PriorityQueue();
      foreach ($values as list($value, $priority)) {
         $pq->insert($value, $priority);
      }

      $values = [];
      while ($pq->valid()) {
         $values[] = $pq->current();
         $pq->next();
      }

      $this->assertEquals(['one', 'two', 'three', 'four'], $values);
   }
}

