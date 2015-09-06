<?php

namespace sndsgd;


/**
 * @coversDefaultClass \sndsgd\TypeTest
 */
class TypeTestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::nullableString
     */
    public function testNullableString()
    {
        $this->assertNull(TypeTest::nullableString(null, "name"));
        $this->assertEquals("test", TypeTest::nullableString("test", "name"));
    }

    /**
     * @covers ::nullableString
     * @expectedException InvalidArgumentException
     */
    public function testNullableStringException()
    {
        TypeTest::nullableString(42, "name");
    }
}
