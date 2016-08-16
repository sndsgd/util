<?php

namespace sndsgd;

class NetTest extends \PHPUnit_Framework_TestCase
{
    public function testPing()
    {
        $this->assertTrue(Net::ping("google.com", 443));
        $this->assertFalse(Net::ping("999.999.999.999", 80, 1));
    }
}
