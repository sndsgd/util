<?php

use \sndsgd\Arr;
use \sndsgd\Url;


class UrlTest extends PHPUnit\Framework\TestCase
{
    private static $urlString =
        'http://user:pass@example.com:1234/some/path?one=1&two=2#frag';

    private static $urlArray = [
        'scheme' => 'http',
        'user' => 'user',
        'pass' => 'pass',
        'host' => 'example.com',
        'port' => 1234,
        'path' => '/some/path',
        'query' => ['one' => 1, 'two' => 2],
        'fragment' => 'frag'
    ];

    public function testEncodeAndDecodeQueryString()
    {
        $tests = [
            ['a' => '"one, two, three !@#$%^&*()"'],
            [
                'one' => [
                    'n*Hn/Ae3wc# zKN6=tdJ*R]B ExGbhn)LTv [4M7*DM4',
                    'another'
                ],
                'two' => '*w8hc4EGUMK    a4^DjR/mV,6}[g^cyM,B  UJtQ*jsbWKC9MCJWZxC'
            ]
        ];

        foreach ($tests as $test) {
            $encoded = Url::encodeQueryString($test);
            $decoded = Url::decodeQueryString($encoded);
            $this->assertEquals($test, $decoded);
            $encoded = Url::encodeQueryString($test, Url::RFC_1866);
            $decoded = Url::decodeQueryString($encoded, Url::RFC_1866);
            $this->assertEquals($test, $decoded);
        }
    }

    public function testEncodeQueryStringException()
    {
        $this->expectException(\InvalidArgumentException::class);
        Url::encodeQueryString([ "forty-two" => 42 ], null);
    }

    public function testDecodeQueryStringException()
    {
        $this->expectException(\InvalidArgumentException::class);
        Url::decodeQueryString("forty-two=42", null);
    }

    public function testCreateFromString()
    {
        $test = ' '.self::$urlString;
        $url = Url::createFromString($test);

        foreach (self::$urlArray as $key => $value) {
            $fn = 'get'.ucfirst($key);
            $this->assertEquals($value, call_user_func([$url, $fn]));
        }

        $test = ' ';
        $url = Url::createFromString($test);
        foreach (self::$urlArray as $key => $value) {
            $fn = 'get'.ucfirst($key);
            if ($key === 'query') {
                $this->assertEquals([], call_user_func([$url, $fn]));
            }
            else {
                $this->assertNull(call_user_func([$url, $fn]));
            }
        }
    }

    public function testCreateFromStringException()
    {
        $this->expectException(\InvalidArgumentException::class);
        Url::createFromString(123);
    }

    public function testCreateFromStringParseFailException()
    {
        $this->expectException(\InvalidArgumentException::class);
        Url::createFromString("nope:///");
    }

    public function testCreateFromArray()
    {
        $url = Url::createFromArray(self::$urlArray);
        $this->assertEquals(self::$urlString, $url->__toString());
    }

    public function testSetPassException()
    {
        $url = new Url();
        $this->expectException(\InvalidArgumentException::class);
        $url->setPass(123);
    }

    public function testSetHostException()
    {
        $url = new Url();
        $this->expectException(\InvalidArgumentException::class);
        $url->setHost([]);
    }

    public function testSetPort()
    {
        $tests = [
            [1234, 1234],
            ['1234', 1234]
        ];

        $url = new Url;
        foreach ($tests as $test) {
            list($test, $expect) = $test;
            $url->setPort($test);
            $this->assertEquals($expect, $url->getPort());
        }
    }

    public function testSetPortException()
    {
        $url = new Url();
        $this->expectException(\InvalidArgumentException::class);
        $url->setPort('123.4');
    }

    public function testSetPath()
    {
        $tests = [
            ['some/path', '/some/path'],
            ['/some/path', '/some/path'],
            [null, null],
        ];

        $url = new Url;

     foreach ($tests as $test) {
            list($test, $expect) = $test;
            $url->setPath($test);
            $this->assertEquals($expect, $url->getPath());
        }
    }

    public function testSetPathException()
    {
        $url = new Url();
        $this->expectException(\InvalidArgumentException::class);
        $url->setPath([]);
    }

    public function testAddQueryData()
    {
        $url = new Url;
        $data = ['one' => 1, 'two' => 2];
        $url->addQueryData((object) $data);
        $this->assertEquals($data, $url->getQuery());

        $url->addQueryData('one=asd');
        Arr::addValue($data, 'one', 'asd');
        $this->assertEquals($data, $url->getQuery());
    }

    public function testAddQueryDataException()
    {
        $url = new Url();
        $this->expectException(\InvalidArgumentException::class);
        $url->addQueryData(123);
    }


    public function testSetFragment()
    {
        $tests = [
            ['#test', 'test'],
            ['some-test', 'some-test'],
            [null, null]
        ];

        $url = new Url;
        foreach ($tests as $test) {
            list($test, $expect) = $test;
            $url->setFragment($test);
            $this->assertEquals($expect, $url->getFragment());
        }
    }

    public function testSetFragmentException()
    {
        $url = new Url;
        $this->expectException(\InvalidArgumentException::class);
        $url->setFragment([]);
    }

    public function testToArray()
    {
        $test = 'http://usr:pass@ex.com:123/some/path?one=1&two=2#frag';
        $expect = self::$urlArray;
        $url = Url::createFromString(self::$urlString);
        $this->assertEquals($expect, $url->__toArray());

        $url->setQuery(null);
        $expect['query'] = [];
        $this->assertEquals($expect, $url->__toArray());
    }

    public function testToString()
    {
        $test = ' http://usr:pass@ex.com:123/some/path?one=1&two=2#frag';
        $expect = trim($test);
        $url = Url::createFromString($test);
        $this->assertEquals($expect, $url->__toString());

        # prepend a slash to the path
        $test = 'http://usr:pass@ex.com:123?one=1&two=2#frag';
        $expect = 'http://usr:pass@ex.com:123/?one=1&two=2#frag';
        $url = Url::createFromString($test);
        $this->assertEquals($expect, (string) $url);
    }

    public function testUpdateFromArray()
    {
        $test = 'http://example.com/some/path?one=1&two=2';
        $expect = 'http://user:pass@example.com:1234/some/path?one=1&two=2#test';

        $url = Url::createFromString($test);
        $url->merge([
            'user' => 'user',
            'pass' => 'pass',
            'host' => 'should-not-copy.com',
            'port' => 1234,
            'fragment' => 'test'
        ]);

        $this->assertEquals($expect, $url->__toString());

    }

    public function testUpdateFromString()
    {
        $test = 'http://example.com/some/path?one=1&two=2';
        $expect = 'http://user:pass@example.com:1234/some/path?one=1&two=2#test';
        $url = Url::createFromString($test);
        $url->merge('http://user:pass@wonky.org:1234/some/path#test');
        $this->assertEquals($expect, $url->__toString());

        # original has empty query
        $test = 'http://example.com/some/path';
        $expect = 'http://user:pass@example.com:1234/some/path?one=1&two=2#test';
        $url = Url::createFromString($test);
        $url->merge('http://user:pass@wonky.org:1234/some/path?one=1&two=2#test');
        $this->assertEquals($expect, $url->__toString());

    }

    public function testUpdateException()
    {
        $url = Url::createFromString(self::$urlString);
        $this->expectException(\InvalidArgumentException::class);
        $url->merge(123);
    }
}