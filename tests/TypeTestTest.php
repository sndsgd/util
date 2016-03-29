<?php

namespace sndsgd;


/**
 * @coversDefaultClass \sndsgd\TypeTest
 */
class TypeTestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::nullableString
     * @dataProvider providerNullableString
     */
    public function testNullableString($test)
    {
        $this->assertSame($test, TypeTest::nullableString($test, "name"));
    }

    public function providerNullableString()
    {
        return [
            [null],
            ["string"],
            [Str::random(100)],
        ];
    }

    /**
     * @covers ::nullableInt
     * @expectedException InvalidArgumentException
     */
    public function testNullableStringException()
    {
        TypeTest::nullableString(42, "name");
    }

    /**
     * @covers ::nullableInt
     * @dataProvider providernullableInt
     */
    public function testNullableInt($test)
    {
        $this->assertSame($test, TypeTest::nullableInt($test, "name"));
    }

    public function providerNullableInt()
    {
        return [
            [null],
            [0],
            [-1],
            [PHP_INT_MAX],
        ];
    }

    /**
     * @covers ::nullableInt
     * @expectedException InvalidArgumentException
     */
    public function testnullableIntException()
    {
        TypeTest::nullableInt("string", "name");
    }
}
