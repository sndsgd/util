<?php

use \sndsgd\Network;


class NetworkTest extends PHPUnit_Framework_TestCase
{
   public function testPing()
   {
      $this->assertTrue(Network::ping('google.com', 443));
      $this->assertFalse(Network::ping('999.999.999.999', 80, 1) === true);
   }
}

