<?php

use \sndsgd\Str;


/**
 * @coversDefaultClass \sndsgd\Str
 */
class StrTest extends PHPUnit_Framework_TestCase
{
    public function testBeginsWith()
    {
        $this->assertTrue(Str::beginsWith("hello there", "he"));
        $this->assertFalse(Str::beginsWith("hello there", " "));
        $this->assertFalse(Str::beginsWith("hello there", "H", true));
    }

    public function testEndsWith()
    {
        $this->assertTrue(Str::endsWith("hello there", "ere"));
        $this->assertFalse(Str::endsWith("hello there", " "));
        $this->assertFalse(Str::endsWith("hello there", "E", true));
        $this->assertTrue(Str::endsWith("hello therE", "E", true));

        $haystack = "Wed, 10 Dec 2014 02:54:32 +0000";
        $needle = "+0000";
        $this->assertTrue(Str::endsWith($haystack, $needle));
    }

    /**
     * @covers ::before
     * @dataProvider beforeProvider
     */
    public function testBefore($haystack, $needle, $expect)
    {
        $result = Str::before($haystack, $needle);
        $this->assertEquals($result, $expect);
    }

    public function beforeProvider()
    {
        return [
            ["application/json; charset=utf8", ";", "application/json"],
            ["some string", "string", "some "],
            ["original string", "not in string", "original string"],
        ];
    }

    /**
     * @covers ::after
     * @dataProvider afterProvider
     */
    public function testAfter($haystack, $needle, $expect)
    {
        $result = Str::after($haystack, $needle);
        $this->assertEquals($result, $expect);
    }

    public function afterProvider()
    {
        return [
            ["application/json; charset=utf8", ";", " charset=utf8"],
            ["some string", "some", " string"],
            ["original string", "not in string", "original string"],
        ];
    }

    /**
     * @dataProvider providerRandom
     */
    public function testRandom($length)
    {
        $result = Str::random($length);
        $this->assertEquals($length, strlen($result));
    }

    public function providerRandom()
    {
        return [
            [1],
            [2],
            [3],
            [4],
            [5],
            [100],
            [1000],
        ];
    }

    public function testToNumber()
    {
        $this->assertEquals(123, Str::toNumber("123"));
        $this->assertEquals(0, Str::toNumber(""));
        $this->assertEquals(-1.23, Str::toNumber("-1.23"));
        $this->assertEquals(-1100, Str::toNumber("-1100"));
        $this->assertEquals(1420, Str::toNumber("1,420.00"));
    }

    public function testToBoolean()
    {
        $tests = [
            ["TRUE", "assertTrue"],
            ["true", "assertTrue"],
            ["1", "assertTrue"],
            [1, "assertTrue"],
            [true, "assertTrue"],

            ["FALSE", "assertFalse"],
            ["false", "assertFalse"],
            ["0", "assertFalse"],
            [0, "assertFalse"],
            [false, "assertFalse"],
            ["", "assertFalse"],

            ["jimbo", "assertNull"],
            [100, "assertNull"],
            [-100, "assertNull"]
        ];

        foreach ($tests as $test) {
            list($test, $method) = $test;
            $result = Str::toBoolean($test);
            $err = 
                "test: ".var_export($test, true).
                " result: ".var_export($result, true);
            $this->$method($result, $err);
        }
    }

    public function testToCamelCase()
    {
        $tests = [
            ["camel-case", "camelCase"],
            ["camel_case", "camelCase"],
            ["camel case", "camelCase"],
            [" camel case_long", "camelCaseLong"],
        ];

        foreach ($tests as $test) {
            list($test, $expect) = $test;
            $this->assertEquals($expect, Str::toCamelCase($test)); 
        }
    }

    public function testToSnakeCase()
    {
        $tests = [
            [" snake-case", "snake_case"],
            ["snake--case", "snake_case"],
            ["snake- -case", "snake_case"],
            ["snake case", "snake_case"],
            ["snakeCase", "snake_case"],
            ["snakeCaseLong", "snake_case_long"]
        ];
        
        foreach ($tests as $test) {
            list($test, $expect) = $test;
            $this->assertEquals($expect, Str::toSnakeCase($test));   
        }

        $this->assertEquals("SNAKE_CASE", Str::toSnakeCase("snakeCase", true));
    }

    public function testStripPostNewlineTabs()
    {
        $tests = [
            "one\n\ttwo\n\t\t\tthree" => "one\ntwo\nthree",
            "one\n\t\t\ntwo" => "one\n\ntwo"
        ];

        foreach ($tests as $test => $expect) {
            $result = Str::stripPostNewlineTabs($test);
            $this->assertEquals($expect, $result);
        }
    }

    public function testStripEmptyLines()
    {
        $tests = [
            "one\n\ntwo" => "one\ntwo",
            "one\n \ntwo\n\t\nthree\n \t \nfour" => "one\ntwo\nthree\nfour"
        ];

        foreach ($tests as $test => $expect) {
            $result = Str::stripEmptyLines($test);
            $this->assertEquals($expect, $result);
        }
    }

    /**
     * @dataProvider providerSummarize
     */
    public function testSummarize($str, $maxLength, $ellipsis, $needle, $expect)
    {
        $result = Str::summarize($str, $maxLength, $ellipsis, $needle);
        $this->assertSame($expect, $result);
    }

    public function providerSummarize()
    {
        # 45 chars long
        $test = "one two three. four fix six seven eight nine.";
        return [
            # max length is >= length
            [$test, 45, "...", "", $test],
            [$test, 46, "...", "", $test],

            # working as expected
            [$test, 25, "...", "", "one two three. four fi..."],
            [$test, 25, "...", " ", "one two three. four..."],
            [$test, 25, "...", ". ", "one two three..."],

            # no spaces; fallback to exact
            ["abcdefghijklmno", 10, "...", " ", "abcdefg..."],
            # no period in substring; fallback to exact
            [$test, 15, "...", ".", "one two thre..."],
        ];
    }

    public function testReplace()
    {
        $values = ["1" => "one", "2" => "two", "3" => "three"];
        $this->assertEquals("one two three", Str::replace("1 2 3", $values));
    }
}
