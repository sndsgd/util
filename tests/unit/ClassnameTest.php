<?php

use \sndsgd\Classname;


class ClassnameTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideMagicToString
     */
    public function testMagicToString($classname, $expect)
    {
        $cn = new Classname($classname);
        $this->assertSame($expect, (string) $cn);
    }

    public function provideMagicToString(): array
    {
        return [
            ["one", "\\one"],
            ["\\one", "\\one"],
            ["\\one\\two", "\\one\\two"],
        ];
    }

    /**
     * @dataProvider provideGetClass
     */
    public function testGetClass($classname, $expect)
    {
        $cn = new Classname($classname);
        $this->assertSame($expect, $cn->getClass());
    }

    public function provideGetClass(): array
    {
        return [
            ["one", "one"],
            ["one\\two", "two"],
            ["\\one\\two", "two"],
        ];
    }

    /**
     * @dataProvider provideGetNamespace
     */
    public function testGetNamespace($classname, $expect)
    {
        $cn = new Classname($classname);
        $this->assertSame($expect, $cn->getNamespace());
    }

    public function provideGetNamespace(): array
    {
        return [
            ["one", ""],
            ["one\\two", "one"],
            ["\\one\\two", "one"],
            ["\\one\\two\\three", "one\\two"],
        ];
    }


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
