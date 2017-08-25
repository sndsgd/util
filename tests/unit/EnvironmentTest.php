<?php

namespace sndsgd;

/**
 * @coversDefaultClass \sndsgd\Environment
 */
class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::validateNodeType
     * @covers ::getNodeType
     * @covers ::getEmulatedNodeType
     * @dataProvider providerConstructor
     */
    public function testConstructor(
        $values,
        $emulateNodeType,
        $expectedException = ""
    )
    {
        if ($expectedException) {
            $this->setExpectedException($expectedException);
        }

        $env = new Environment($values, $emulateNodeType);
        $rc = new \ReflectionClass($env);

        $property = $rc->getProperty("values");
        $property->setAccessible(true);
        $this->assertSame($values, $property->getValue($env));

        $nodeType = $values[Environment::SYSTEM_ENVVAR_NAME];
        $this->assertSame($nodeType, $env->getNodeType());
        $this->assertSame($emulateNodeType, $env->getEmulatedNodeType());
    }

    public function providerConstructor()
    {
        $dev = Environment::DEV;
        $stage = Environment::STAGE;
        $prod = Environment::PROD;

        $emptyValues = [Environment::SYSTEM_ENVVAR_NAME => "", "test" => 42];
        $devValues = [Environment::SYSTEM_ENVVAR_NAME => $dev, "test" => 42];
        $stageValues = [Environment::SYSTEM_ENVVAR_NAME => $stage, "test" => 42];
        $prodValues = [Environment::SYSTEM_ENVVAR_NAME => $prod, "test" => 42];

        return [
            # missing system environment variable
            [$emptyValues, "", \InvalidArgumentException::class],
            [$prodValues, ""],
            [$prodValues, $dev],
            [$devValues, ""],
            [$devValues, $prod],

            # attempt to emulate same node type as actual node type
            [$devValues, $dev, \LogicException::class],
        ];
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        $values = [
            Environment::SYSTEM_ENVVAR_NAME => Environment::PROD,
            "one" => \sndsgd\Str::random(42),
            "two" => \sndsgd\Str::random(42),
        ];

        $env = new Environment($values);
        $this->assertSame($values["one"], $env->get("one"));
        $this->assertSame($values["two"], $env->get("two"));
    }

    /**
     * @covers ::getValues
     */
    public function testGetValues()
    {
        $values = [
            Environment::SYSTEM_ENVVAR_NAME => Environment::PROD,
            \sndsgd\Str::random(42) => \sndsgd\Str::random(42),
            \sndsgd\Str::random(42) => \sndsgd\Str::random(42),
        ];

        $env = new Environment($values);
        $this->assertSame($values, $env->getValues());
    }

    /**
     * @dataProvider providerIsEmulated
     * @covers ::isEmulated
     */
    public function testIsEmulated($values, $emulate, $expect)
    {
        $env = new Environment($values, $emulate);
        $this->assertSame($expect, $env->isEmulated());
    }

    public function providerIsEmulated()
    {
        $values = [Environment::SYSTEM_ENVVAR_NAME => Environment::DEV];
        return [
            [$values, Environment::PROD, true],
            [$values, Environment::STAGE, true],
            [$values, "", false],
        ];
    }

    /**
     * @dataProvider provideIsCli
     * @covers ::isCli
     */
    public function testIsCli(string $sapi, bool $expect)
    {
        $environment = new Environment([], Environment::PROD, $sapi);
        $this->assertSame($expect, $environment->isCli());
    }

    public function provideIsCli(): array
    {
        return [
            ["cli", true],
            ["cgi", false],
            ["cgi-fcgi", false],
        ];
    }

    /**
     * @covers ::isDev
     * @dataProvider providerIsDev
     */
    public function testIsDev($nodeType, $emulateNodeType, $expect)
    {
        $values = [Environment::SYSTEM_ENVVAR_NAME => $nodeType];
        $env = new Environment($values, $emulateNodeType);
        $this->assertSame($expect, $env->isDev());
    }

    public function providerIsDev()
    {
        $dev = Environment::DEV;
        $stage = Environment::STAGE;
        $prod = Environment::PROD;

        return [
            [$dev, "", true],
            [$stage, $dev, true],
            [$prod, $dev, true],
            [$dev, $stage, false],
            [$dev, $prod, false],
        ];
    }

    /**
     * @covers ::isProd
     * @dataProvider providerIsProd
     */
    public function testIsProd($nodeType, $emulateNodeType, $expect)
    {
        $values = [Environment::SYSTEM_ENVVAR_NAME => $nodeType];
        $env = new Environment($values, $emulateNodeType);
        $this->assertSame($expect, $env->isProd());
    }

    public function providerIsProd()
    {
        $dev = Environment::DEV;
        $stage = Environment::STAGE;
        $prod = Environment::PROD;

        return [
            [$prod, "", true],
            [$stage, "", true],
            [$prod, $stage, true],
            [$stage, $prod, true],
            [$dev, $stage, true],
            [$dev, $prod, true],
            [$dev, "", false],
        ];
    }
}
