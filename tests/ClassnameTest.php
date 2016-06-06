<?php

use \sndsgd\Classname;


class ClassnameTest extends PHPUnit_Framework_TestCase
{
    public function testSplit()
    {
        $res = Classname::split("one.two.three");
        $this->assertEquals(["one", "two", "three"], $res);
    }

    public function testToString()
    {
        $res = Classname::toString("one.two.three");
        $this->assertEquals("one\\two\\three", $res);

        $res = Classname::toString("one\\two\\three", ".");
        $this->assertEquals("one.two.three", $res);

        $res = Classname::toString(["one","two","three"]);
        $this->assertEquals("one\\two\\three", $res);      
    }

    public function testToMethod()
    {
        $res = Classname::toMethod("some.namespace.method");
        $this->assertEquals("some\\namespace::method", $res);

        $res = Classname::toMethod(["some","namespace","method"]);
        $this->assertEquals("some\\namespace::method", $res);      
    }

    /**
     * @dataProvider providerFromContents
     */
    public function testFromContents($contents, $expect)
    {
        $this->assertSame($expect, Classname::fromContents($contents));
    }

    public function providerFromContents()
    {
        return [
            [
                "<?php\nclass TestClass {\n\n}\n\n?>",
                "TestClass",
            ],
            [
                "<?php\nclass TestClass extends AnotherClass\n{\n\n}\n\n?>",
                "TestClass",
            ],
            [
                "<?php\nnamespace ns;\n\nclass Classname {\n\n}\n",
                "ns\\Classname"
            ],
            [
                "<?php\nnamespace test\\this;\n\n?>",
                "",
            ],

        ];
    }
}
