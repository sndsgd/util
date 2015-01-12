<?php

namespace sndsgd;


class Network
{
   /**
    * Ping a host
    * 
    * @param string $host An ip address or hostname
    * @param integer $port The port to connect to
    * @param integer $timeout Seconds to allow a connection attempt
    * @return boolean|array
    * @return boolean:true The ping succeeded
    * @return string An error was encountered; format: "code: message"
    */
   public static function ping($host, $port, $timeout = 5)
   {
      $fp = @fsockopen($host, $port, $errCode, $errStr, $timeout);
      if ($fp) {
         fclose($fp);
         return true;
      }
      return "$errCode: $errStr";
   }   
}

