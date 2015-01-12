<?php

use \sndsgd\PriorityQueue;


class PriorityQueueTest extends PHPUnit_Framework_TestCase
{
   public function setUp()
   {
      $values = [
         ['three', 1],
         ['two', 50],
         ['four', 1],
         ['one', 100]
      ];

      $this->pq = new PriorityQueue();
      foreach ($values as $value) {
         list($value, $priority) = $value;
         $this->pq->insert($value, $priority);
      }
   }

   public function testNext()
   {
      $values = [];
      while ($this->pq->valid()) {
         $values[] = $this->pq->current();
         $this->pq->next();
      }
      $this->assertEquals(['one', 'two', 'three', 'four'], $values);
   }

   public function testExtract()
   {
      $values = [];
      while ($this->pq->valid()) {
         $values[] = $this->pq->extract();
      }
      $this->assertEquals(['one', 'two', 'three', 'four'], $values);
   }
}

