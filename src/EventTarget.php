<?php

namespace sndsgd\util;

use \Closure;
use \InvalidArgumentException;


class EventTarget
{
   /**
    * Handler functions, keyed by event type
    * 
    * @var array.<string,array.<callable>>
    */
   protected $eventHandlers_ = [];

   /**
    * Add an event handler
    * 
    * @param string $event The name of the event
    * @param string|callable $handler The function that handles the event
    * @param boolean $unshift Add the handler at the top of the stack
    * @return sndsgd\util\EventTarget
    * @throws InvalidArgumentException If the event isn't a string
    */
   public function on($event, $handler, $unshift = false)
   {
      if (!is_string($event)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'event'; ".
            "expecting an event name as string"
         );
      }

      if (!array_key_exists($event, $this->eventHandlers_)) {
         $this->eventHandlers_[$event] = [];
      }

      if ($unshift) {
         array_unshift($handler, $this->eventHandlers_[$event]);
      }
      else {
         $this->eventHandlers_[$event][] = $handler;
      }
      return $this;
   }

   /**
    * Call all handlers for a given event
    *
    * Note: if a handler returns boolean false, any remaining handlers are skipped
    * @param string $event The name of the eevent to fire
    * @param array $args The arguments to pass to every handler
    * @return boolean
    * @return boolean:false A handler returned false
    * @return boolean:true All handlers returned true or no handlers exist
    * @throws InvalidArgumentException If the event isn't a string
    */
   public function fire($event, array $args = [])
   {
      if (!is_string($event)) {
         throw new InvalidArgumentException(
            "invalid value provided for 'event'; ".
            "expecting an event name as string"
         );
      }
      else if (array_key_exists($event, $this->eventHandlers_)) {
         foreach ($this->eventHandlers_[$event] as $handler) {
            if (call_user_func_array($handler, $args) === false) {
               return false;
            }
         }   
      }
      return true;
   }
}


