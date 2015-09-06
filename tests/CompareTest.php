<?php

use \sndsgd\Compare;


/**
 * @coversDefaultClass \sndsgd\Compare
 */
class CompareTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::equal
     */
    public function testEqual()
    {
        $this->assertTrue(Compare::equal(1, 1));
        $this->assertTrue(Compare::equal(1, "1"));
        $this->assertTrue(Compare::equal("1", 1));
        $this->assertTrue(Compare::equal(1, true));
        $this->assertTrue(Compare::equal(true, true));

        $this->assertFalse(Compare::equal(1, 0));
        $this->assertFalse(Compare::equal(1, "0"));
        $this->assertFalse(Compare::equal("1", false));
        $this->assertFalse(Compare::equal(1, false));
        $this->assertFalse(Compare::equal(true, false));
    }

    /**
     * @covers ::strictEqual
     */
    public function testStrictEqual()
    {
        $this->assertTrue(Compare::strictEqual(1, 1));
        $this->assertTrue(Compare::strictEqual(true, true));
        $this->assertTrue(Compare::strictEqual(false, false));

        $this->assertFalse(Compare::strictEqual(1, "1"));
        $this->assertFalse(Compare::strictEqual(true, false));
    }

    /**
     * @covers ::getMethod
     */
    public function testGetMethod()
    {
        $similar = "sndsgd\\Compare::equal";
        $same = "sndsgd\\Compare::strictEqual";

        $this->assertEquals($similar, Compare::getMethod());
        $this->assertEquals($similar, Compare::getMethod(false));
        $this->assertEquals($same, Compare::getMethod(true));
    }
}