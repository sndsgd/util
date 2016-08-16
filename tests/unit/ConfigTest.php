<?php

namespace sndsgd;

/**
 * @coversDefaultClass \sndsgd\Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected static $values = [
        "string" => "testing",
        "integer" => 42,
        "float" => 4.2,
        "array" => [
            "qDERhNa1S0Pwa5GsmkzHZxDwv9DBAqzOLxCdQh3VuF",
            "mKJcR2DSvcshn5K8SGlqxOnTRUOBmYj1eQD72gZbL4",
        ],
    ];

    protected $config;

    public function setUp()
    {
        $this->config = new Config(static::$values);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        $config = new Config(static::$values);
        $this->assertInstanceOf(Config::class, $config);
    }

    /**
     * @covers ::get
     * @dataProvider providerGet
     */
    public function testGet($key, $default, $expect)
    {
        $this->assertSame($expect, $this->config->get($key, $default));
    }

    public function providerGet()
    {
        $rand = \sndsgd\Str::random(42);
        return [
            ["string", "", static::$values["string"]],
            ["integer", 0, static::$values["integer"]],
            ["array", [], static::$values["array"]],
            ["missing", $rand, $rand],
        ];
    }

    /**
     * @dataProvider providerGetRequired
     * @covers ::getRequired
     */
    public function testGetRequired($key, $expect, $expectException = false)
    {
        if ($expectException) {
            $this->setExpectedException(\RuntimeException::class);
        }
        $this->assertSame($expect, $this->config->getRequired($key));
    }

    public function providerGetRequired()
    {
        return [
            ["string", static::$values["string"], false],
            ["nope", "", true],
        ];
    }
}
