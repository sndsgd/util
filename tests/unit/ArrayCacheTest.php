<?php

namespace sndsgd;

/**
 * @coversDefaultClass \sndsgd\ArrayCache
 */
class ArrayCacheTest extends \PHPUnit_Framework_TestCase
{
    protected $cache;

    public function setup()
    {
        $this->cache = new ArrayCache();
    }

    /**
     * @covers ::set
     * @covers ::get
     * @dataProvider provideSetGet
     */
    public function testSetGet($group, $key, $value)
    {
        $this->cache->set($group, $key, $value);
        $this->assertSame($value, $this->cache->get($group, $key));
    }

    public function provideSetGet(): array
    {
        return [
            ["group", "stringKey", "abc"],
            ["group", "arrayKey", []],
            ["group", "objectKey", (new \StdClass())],
        ];
    }

    /**
     * @covers ::set
     * @expectedException \InvalidArgumentException
     */
    public function testSetGroupException()
    {
        $this->cache->set("", "", null);
    }

    /**
     * @covers ::set
     * @expectedException \InvalidArgumentException
     */
    public function testSetKeyException()
    {
        $this->cache->set("group", "", null);
    }

    /**
     * @covers ::set
     * @expectedException \InvalidArgumentException
     */
    public function testSetValueException()
    {
        $this->cache->set("group", "key", null);
    }

    /**
     * @covers ::remove
     * @dataProvider provideRemove
     */
    public function testRemove(array $values, $group, $key, array $expectValues)
    {
        $cache = new ArrayCache();
        $reflection = new \ReflectionClass($cache);
        $property = $reflection->getProperty("values");
        $property->setAccessible(true);
        $property->setValue($cache, $values);

        $cache->remove($group, $key);
        $this->assertSame($expectValues, $property->getValue($cache));
    }

    public function provideRemove(): array
    {
        $values = [
            "a" => [
                "aa" => 1,
                "ab" => 2,
            ],
            "b" => [
                "ba" => [],
            ],
        ];

        return [
            [$values, "a", "aa", ["a" => ["ab" => 2], "b" => ["ba" => []]]],
            [$values, "a", "", ["b" => ["ba" => []]]],
        ];
    }

    /**
     * @covers ::remove
     * @expectedException \LogicException
     */
    public function testRemoveEmptyGroupException()
    {
        $this->cache->remove("", "key");
    }
}
