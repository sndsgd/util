<?php

namespace sndsgd;

use \ReflectionClass;


/**
 * @coversDefaultClass \sndsgd\Storage
 */
class StorageTest extends \PHPUnit_Framework_TestCase
{
    public static function getInaccessibleProperty($object, $property)
    {
        $rc = new ReflectionClass($object);
        $prop = $rc->getProperty($property);
        $prop->setAccessible(true);
        return $prop;
    }

    /**
     * @coversNothing
     */
    protected static function reset()
    {
        $storage = Storage::getInstance();
        $property = static::getInaccessibleProperty($storage, "values");
        $property->setValue($storage, []);
    }

    /**
     * @coversNothing
     */
    public static function tearDownAfterClass()
    {
        static::reset();
    }

    /**
     * @coversNothing
     */
    public function setUp()
    {
        static::reset();
    }

    /**
     * @covers ::__callStatic
     */
    public function testCallStatic()
    {
        $storage = Storage::getInstance();
        $value = new \StdClass();
        $storage->set("test", $value);
        $this->assertEquals($value, Storage::test());
    }

    /**
     * @covers ::import
     * @covers ::export
     * @dataProvider providerImportExport
     */
    public function testImportExport($values)
    {
        $storage = Storage::getInstance();
        $storage->import($values);
        $this->assertEquals($values, $storage->export());
    }

    public function providerImportExport()
    {
        return [
            [["one" => 1, "two" => 2.0, "object" => new \StdClass()]],
        ];
    }

    /**
     * @covers ::has
     */
    public function testHas()
    {
        $storage = Storage::getInstance();
        $key = "test";

        $this->assertFalse($storage->has($key));

        $values = static::getInaccessibleProperty($storage, "values");
        $values->setValue($storage, [ $key => true ]);
        $this->assertTrue($storage->has($key));
    }

    /**
     * @covers ::set
     */
    public function testSet()
    {
        $storage = Storage::getInstance();
        $values = static::getInaccessibleProperty($storage, "values");

        $key = Str::random(512);
        $this->assertArrayNotHasKey($key, $values->getValue($storage));
        $storage->set($key, true);
        $this->assertArrayHasKey($key, $values->getValue($storage));
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        $storage = Storage::getInstance();
        $values = static::getInaccessibleProperty($storage, "values");

        $key = "test";
        $values->setValue($storage, [ $key => true ]);
        $this->assertTrue($storage->get($key));
        $values->setValue($storage, [ $key => 42 ]);
        $this->assertEquals(42, $storage->get($key));
    }

    /**
     * @covers ::get
     * @expectedException \Exception
     */
    public function testGetException()
    {
        Storage::getInstance()->get("does not exist");
    }
}
