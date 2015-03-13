<?php

namespace sndsgd;

use \Exception;
use \InvalidArgumentException;


class Timer
{
   /**
    * All timers that are given a name are referenced here
    * 
    * @var array<string,Timer>
    */
   private static $timers = [];

   /**
    * Reset the array of referenced timers
    * 
    * @return void
    */
   public static function reset()
   {
      self::$timers = [];
   }

   /**
    * Get all durations for named timers
    * 
    * @param integer $precision 
    * @return array<string|float|integer>
    */
   public static function exportDurations($precision = -1)
   {
      $ret = [];
      foreach (self::$timers as $timer) {
         $ret[$timer->getName()] = $timer->getDuration($precision);
      }
      return $ret;
   }

   /**
    * Create a timer, give it a name, and start it
    *
    * @param string $name A name to reference the timer
    */
   public static function create($name)
   {
      $timer = new Timer;
      $timer->setName($name);
      $timer->start();
      return $timer;
   }

   /**
    * A nickname for the timer
    * 
    * @var string|null
    */
   protected $name;

   /**
    * The start time in microseconds
    * 
    * @var float|null
    */
   protected $startTime;

   /**
    * The start time in microseconds
    * 
    * @var float|null
    */
   protected $stopTime;

   /**
    * The duration in microseconds
    * 
    * @var float|null
    */
   protected $duration;

   /**
    * Convert the object into a string
    * 
    * @return string
    */
   public function __toString()
   {
      $name = $this->getName();
      if ($name === null) {
         $name = 'task';
      }

      $time = $this->getDuration(5);
      return ($this->getStopTime() === null)
         ? "{$name} has consumed {$time} seconds so far"
         : "{$name} took {$time} seconds";
   }

   /**
    * Start the timer
    * 
    * @return void
    */
   public function start()
   {
      $this->startTime = microtime(true);
   }

   /**
    * Stop the timer and calculate the duration
    * 
    * @return float The timer duration
    */
   public function stop()
   {
      $time = microtime(true);
      if ($this->startTime === null) {
         throw new Exception(
            "failed to stop timer; the timer was never started"
         );
      }
      else if ($this->stopTime === null) {
         $this->stopTime = $time;
         $this->duration = $this->stopTime - $this->startTime;
      }
      return $this->duration;
   }

   /**
    * Get the start time
    * 
    * @return float
    */
   public function getStartTime()
   {
      return $this->startTime;
   }

   /**
    * Set a name for the timer
    *
    * @param string $name
    * @return void
    */
   public function setName($name)
   {
      if ($this->name !== null) {
         unset(self::$timers[$name]);
      }

      if (array_key_exists($name, self::$timers)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'name'; ".
            "expecting a unique name as string ('$name' is already in use)"
         );
      }

      $this->name = $name;
      self::$timers[$name] = $this;
   }

   /**
    * Get the name
    * 
    * @return string|null
    */
   public function getName()
   {
      return $this->name;
   }

   /**
    * Get the stop time
    *
    * @return float|null
    */
   public function getStopTime()
   {
      return $this->stopTime;
   }

   /**
    * Get the current duration
    * 
    * @param integer $precision
    * @return string|float|integer 
    */
   public function getDuration($precision = -1)
   {
      if (!is_int($precision)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'precision'; expecting the number ".
            "of decimal places (use -1 to return the float value)"
         );
      }

      $time = ($this->duration === null)
         ? microtime(true) - $this->startTime
         : $this->duration;

      return ($precision === -1)
         ? $time
         : number_format($time, $precision);
   }
}

