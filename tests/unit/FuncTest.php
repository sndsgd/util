<?php

namespace sndsgd;

class FuncTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerExists
     */
    public function testExists($test, $expect)
    {
        $this->assertSame($expect, Func::exists($test));
    }

    public function providerExists()
    {
        return [
            ["is_string", true],
            ["filesize", true],
            ["_____________nope", false],
            ["DateTime::createFromFormat", true],
            ["sndsgd\\Func::exists", true],
            ["This\\Class\\Doesnt\\Exist::test", false],
        ];
    }

    /**
     * @dataProvider providerGetReflection
     */
    public function testGetReflection($test)
    {
        $result = Func::getReflection($test);
        $this->assertInstanceOf("ReflectionFunctionAbstract", $result);
    }

    public function providerGetReflection()
    {
        return [
            ["strpos"],
            ["sndsgd\\Func::exists"],
            [function() { return true; }],
        ];
    }
}
