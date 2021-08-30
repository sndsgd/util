<?php

namespace sndsgd;


/**
 * @coversDefaultClass \sndsgd\TypeTest
 */
class TypeTestTest extends \PHPUnit\Framework\TestCase
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
     * @covers ::nullableString
     */
    public function testNullableStringException()
    {
        $this->expectException(\InvalidArgumentException::class);
        TypeTest::nullableString(42, "name");
    }

    /**
     * @covers ::nullableInt
     * @dataProvider providerNullableInt
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
     */
    public function testNullableIntException()
    {
        $this->expectException(\InvalidArgumentException::class);
        TypeTest::nullableInt("string", "name");
    }

    /**
     * @covers ::TypedArray
     * @covers ::instanceArray
     * @covers ::scalarArray
     * @dataProvider providerTypedArray
     */
    public function testTypedArray($values, $type, $exception = "")
    {
        if ($exception) {
            $this->expectException($exception);
        }
        $result = TypeTest::typedArray($values, $type);
        $this->assertTrue(is_array($result));
    }

    public function providerTypedArray()
    {
        $ex = \InvalidArgumentException::class;
        return [
            [[true, false, true, false], "bool"],
            [[true, false, true, false, "1"], "bool", $ex],
            [[true, false, true, false, 1], "bool", $ex],
            [[true, false, true, false, 1.0], "bool", $ex],
            [[1.0, 2.0, 3.0], "float"],
            [[1.0, 2.0, 3.0, true], "float", $ex],
            [[1.0, 2.0, 3.0, 1], "float", $ex],
            [[1.0, 2.0, 3.0, "abc"], "float", $ex],
            [[1, 2, 3], "int"],
            [["a", "b", "c"], "string"],
            [[new \stdClass(), new \stdClass()], \stdClass::class],
            [[new \stdClass(), new \SplStack()], \stdClass::class, $ex],
        ];
    }
}
