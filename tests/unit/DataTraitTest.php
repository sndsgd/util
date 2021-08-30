<?php

namespace sndsgd;

class DataTraitExample
{
    use DataTrait;

}


class DataTraitTest extends \PHPUnit\Framework\TestCase
{
    protected $m;
    protected $data = [
        "one" => 1,
        "two" => 2,
        "three" => [1,2,3]
    ];
    public function setUp(): void
    {
        $this->m = new DataTraitExample;
        $this->m->setData($this->data);
    }

    public function testHasData()
    {
        $dt = new DataTraitExample;
        $this->assertFalse($dt->hasData());
        $dt->addData("test", 42);
        $this->assertTrue($dt->hasData());
    }

    public function testAddData()
    {
        $this->m->addData("test", ["value"]);
        $this->assertEquals(["value"], $this->m->getData("test"));

        # test overwrite
        $result = $this->m->addData("one", "one");
        $this->assertInstanceOf("sndsgd\\DataTraitExample", $result);
        $this->assertEquals("one", $this->m->getData("one"));

        # test add array
        $this->m->addData([ "four" => 4, "five" => 5 ]);
        $this->assertEquals(4, $this->m->getData("four"));
        $this->assertEquals(5, $this->m->getData("five"));
    }

    public function testAddDataException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->m->addData(42, "<- the key must be a string");
    }

    public function testRemoveData()
    {
        $this->assertTrue($this->m->removeData("one"));
        $this->assertFalse($this->m->removeData("doesnt-exist"));

        $this->assertTrue($this->m->removeData());
        $this->assertEquals([], $this->m->getData());
    }

    public function testGetData()
    {
        $this->assertEquals($this->data, $this->m->getData());
        $this->assertEquals(1, $this->m->getData("one"));
        $this->assertEquals(2, $this->m->getData("two"));
        $this->assertEquals([1,2,3], $this->m->getData("three"));
        $this->assertNull($this->m->getData("doesnt-exist"));
    }
}