<?php

namespace sndsgd;

class ArrayAbstractTest extends \PHPUnit\Framework\TestCase
{
    private function createAnonymousClass(array $values, $isReadOnly = false)
    {
        return new class($values, $isReadOnly) extends ArrayAbstract {};
    }

    /**
     * @dataProvider providerOffsetExists
     */
    public function testOffsetExists($values, $offset, $expect)
    {
        $test = $this->createAnonymousClass($values);
        $this->assertSame($expect, $test->offsetExists($offset));
        $this->assertSame($expect, isset($test[$offset]));
    }

    public function providerOffsetExists()
    {
        return [
            [[1,2], 0, true],
            [[1,2], 1, true],
            [[1,2], 2, false],
            [["one" => 1, "two" => 2], "one", true],
            [["one" => 1, "two" => 2], "three", false],
        ];
    }

    /**
     * @dataProvider providerOffsetGet
     */
    public function testOffsetGet($values, $offset, $expect)
    {
        $test = $this->createAnonymousClass($values);
        $this->assertSame($expect, $test->offsetGet($offset));
        $this->assertSame($expect, $test[$offset]);
    }

    public function providerOffsetGet()
    {
        return [
            [[1,2], 0, 1],
            [[1,2], 1, 2],
            [[1,2], 2, null],
            [["one" => 1, "two" => 2], "one", 1],
        ];
    }

    /**
     * @dataProvider providerOffsetSet
     */
    public function testOffsetSet($values, $offset)
    {
        if ($offset === null) {
            $test = $this->createAnonymousClass($values);
            $count = count($test);
            $value = Str::random(10);
            $test->offsetSet($offset, $value);
            $this->assertCount($count + 1, $test);

            $count = count($test);
            $value = Str::random(10);
            $test[] = $value;
            $this->assertCount($count + 1, $test);
        } else {
            $test = $this->createAnonymousClass($values);
            $value = Str::random(10);
            $test->offsetSet($offset, $value);
            $this->assertSame($value, $test[$offset]);

            $test = $this->createAnonymousClass($values);
            $value = Str::random(10);
            $test[$offset] = $value;
            $this->assertSame($value, $test[$offset]);
        }
    }

    public function providerOffsetSet()
    {
        return [
            [[], null],
            [[1], 0],
            [[1], 1],
            [["one" => 1, "two" => 2], "one"],
        ];
    }

    public function testOffsetSetReadOnly()
    {
        $test = $this->createAnonymousClass([], true);

        $this->expectException(\RuntimeException::class);
        $test["test"] = "value";
    }

    /**
     * @dataProvider providerOffsetUnset
     */
    public function testOffsetUnset($values, $offset)
    {
        $test = $this->createAnonymousClass([$offset => Str::random(10)]);
        $this->assertTrue(isset($test[$offset]));
        $test->offsetUnset($offset);
        $this->assertFalse(isset($test[$offset]));

        $test = $this->createAnonymousClass([$offset => Str::random(10)]);
        $this->assertTrue(isset($test[$offset]));
        unset($test[$offset]);
        $this->assertFalse(isset($test[$offset]));
    }

    public function providerOffsetUnset()
    {
        return [
            [[], 0],
            [[1], 0],
            [[1], 1],
            [["one" => 1, "two" => 2], "one"],
        ];
    }

    public function testOffsetUnsetReadOnly()
    {
        $test = $this->createAnonymousClass(["test" => 1], true);

        $this->expectException(\RuntimeException::class);
        unset($test["test"]);
    }

    public function testGetIterator()
    {
        $values = [1, "two", 3.3, [4]];
        $test = $this->createAnonymousClass($values);
        $iterator = $test->getIterator();
        $this->assertInstanceOf(\ArrayIterator::class, $iterator);
        foreach ($test as $index => $value) {
            $this->assertSame($value, $values[$index]);
        }
    }
}
