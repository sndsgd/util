<?php

namespace sndsgd;

/**
 * @coversDefaultClass \sndsgd\Str
 */
class StrTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideContains
     */
    public function testContains($haystack, $needle, $caseSensitive, $expect)
    {
        $this->assertSame(
            $expect,
            Str::contains($haystack, $needle, $caseSensitive)
        );
    }

    public function provideContains(): array
    {
        return [
            ["all lowercase", "all", true, true],
            ["all lowercase", "all", false, true],
            ["all lowercase", "case", true, true],
            ["all lowercase", "case", false, true],
            ["all lowercase", " ", true, true],
            ["all lowercase", " ", false, true],
            ["all lowercase", "l l", true, true],
            ["all lowercase", "l l", false, true],
            ["all lowercase", "AlL", true, false],
            ["all lowercase", "casE", true, false],
        ];
    }

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
     * @covers ::getNeedlePosition
     * @dataProvider beforeProvider
     */
    public function testBefore($haystack, $needle, $useLastNeedle, $expect)
    {
        $result = Str::before($haystack, $needle, $useLastNeedle);
        $this->assertEquals($result, $expect);
    }

    public function beforeProvider()
    {
        return [
            ["one two three", " ", false, "one"],
            ["one two three", " ", true, "one two"],
            ["Some\\Namespace\\Classname", "\\", false, "Some"],
            ["Some\\Namespace\\Classname", "\\", true, "Some\\Namespace"],
            ["application/json; charset=utf8", ";", false, "application/json"],
            ["some string", "string", false, "some "],
            ["original string", "not in string", false, "original string"],

        ];
    }

    /**
     * @covers ::after
     * @covers ::getNeedlePosition
     * @dataProvider afterProvider
     */
    public function testAfter($haystack, $needle, $useLastNeedle, $expect)
    {
        $result = Str::after($haystack, $needle, $useLastNeedle);
        $this->assertEquals($result, $expect);
    }

    public function afterProvider()
    {
        return [
            ["one two three", " ", false, "two three"],
            ["one two three", " ", true, "three"],
            ["Some\\Namespace\\Classname", "\\", false, "Namespace\\Classname"],
            ["Some\\Namespace\\Classname", "\\", true, "Classname"],
            ["application/json; charset=utf8", ";", false, " charset=utf8"],
            ["some string", "some", false, " string"],
            ["original string", "not in string", false, "original string"],
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

    /**
     * @dataProvider provideToBoolean
     */
    public function testToBoolean($test, $expect)
    {
        $this->assertSame($expect, Str::toBoolean($test));
    }

    public function provideToBoolean(): array
    {
        return [
            ["true", true],
            ["TRUE", true],
            ["false", false],
            ["FALSE", false],
            ["t", true],
            ["T", true],
            ["f", true],
            ["F", true],
            ["on", true],
            ["ON", true],
            ["off", false],
            ["OFF", false],
            ["1", true],
            ["0", false],

            ["jimbo", null],
            [100, null],
            [-100, null],
            ["", null],
        ];
    }

    /**
     * @dataProvider provideToCamelCase
     */
    public function testToCamelCase($test, $expect)
    {
        $this->assertEquals($expect, Str::toCamelCase($test));
    }

    public function provideToCamelCase(): array
    {
        return [
            ["camel-case", "camelCase"],
            ["camel_case", "camelCase"],
            ["camel case", "camelCase"],
            [" camel case_long", "camelCaseLong"],
        ];
    }

    /**
     * @dataProvider provideToPascalCase
     */
    public function testToPascalCase($test, $expect)
    {
        $this->assertEquals($expect, Str::toPascalCase($test));
    }

    public function provideToPascalCase(): array
    {
        return [
            ["foo-bar", "FooBar"],
            ["foo_bar", "FooBar"],
            ["foo bar", "FooBar"],
            [" foo bar_baz", "FooBarBaz"],
        ];
    }

    /**
     * @dataProvider provideToSnakeCase
     */
    public function testToSnakeCase($test, $expect)
    {
        $this->assertEquals($expect, Str::toSnakeCase($test));
        $this->assertEquals(strtoupper($expect), Str::toSnakeCase($test, true));
    }

    public function provideToSnakeCase(): array
    {
        return [
            [" snake-case", "snake_case"],
            ["snake--case", "snake_case"],
            ["snake- -case", "snake_case"],
            ["snake case", "snake_case"],
            ["snakeCase", "snake_case"],
            ["snakeCaseLong", "snake_case_long"],
        ];
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

    /**
     * @dataProvider provideCountMessage
     */
    public function testCountMessage(
        string $template,
        int $count,
        string $singular,
        string $plural,
        string $expect
    )
    {
        $result = Str::countMessage($template, $count, $singular, $plural);
        $this->assertSame($expect, $result);
    }

    public function provideCountMessage()
    {
        return [
            [
                "found %s %s",
                0,
                "record",
                "records",
                "found 0 records",
            ],
            [
                "found %s %s",
                1,
                "record",
                "records",
                "found 1 record",
            ],
            [
                "found %s %s",
                2,
                "record",
                "records",
                "found 2 records",
            ],
        ];
    }
}
