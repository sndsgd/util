<?php

namespace sndsgd;

class SelfValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideConstructor
     */
    public function testConstructor(
        string $key,
        $values,
        array $requiredKeys,
        array $expectErrors,
        bool $expectIsValid
    )
    {
        $mock = $this->getMockBuilder(SelfValidator::class)
            ->disableOriginalConstructor()
            ->setMethods(["getRequiredKeys"])
            ->getMockForAbstractClass();

        $mock->method("getRequiredKeys")->willReturn($requiredKeys);

        // call the constructor with the appropriate
        $reflection = new \ReflectionClass(SelfValidator::class);
        $constructor = $reflection->getConstructor();
        $constructor->invoke($mock, $key, $values);

        $this->assertSame($expectErrors, $mock->getErrors());
        $this->assertSame($expectIsValid, $mock->isValid());
    }

    /**
     * Data provider for `testConstructor`
     *
     * @return array
     */
    public function provideConstructor(): array
    {
        return [
            [
                "key" => "",
                "values" => 123,
                "requiredKeys" => [],
                "expectErrors" => [
                    SelfValidator::ROOT_ERROR_KEY => ["expecting an array of values"]
                ],
                "expectIsValid" => false,
            ],
            [
                "key" => "test",
                "values" => [],
                "requiredKeys" => ["required"],
                "expectErrors" => [
                    "test.required" => [
                        "this property is required"
                    ],
                ],
                "expectIsValid" => false,
            ],
            [
                "key" => "",
                "values" => ["this_is_unexpected" => 42],
                "requiredKeys" => ["required"],
                "expectErrors" => [
                    "required" => ["this property is required"],
                    "this_is_unexpected" => ["unexpected property"],
                ],
                "expectIsValid" => false,
            ],
            [
                "key" => "",
                "values" => [],
                "requiredKeys" => [],
                "expectErrors" => [],
                "expectIsValid" => true,
            ],
        ];
    }

    /**
     * Ensure that setters are called for properties in the constructor
     */
    public function testVerifyValues()
    {
        $name = "";
        $values = ["test_value" => 123];

        $instance = new class($name, $values) extends SelfValidator
        {
            protected $testValue;

            protected function setTestValue(string $key, $value)
            {
                $this->testValue = $value;
            }

            public function getTestValue()
            {
                return $this->testValue;
            }
        };

        $this->assertTrue($instance->isValid());
        $this->assertSame(123, $instance->getTestValue());
    }

    public function testAddErrors()
    {
        $name = "";
        $values = [];
        $instance = new class($name, $values) extends SelfValidator
        {
            const ERRORS = [
                "zzzz" => ["this will be last due to sorting"],
                "foo" => ["that didn't work"],
                "bar" => ["one error", "two error"],
            ];

            public function __construct(string $key, $values)
            {
                parent::__construct($key, $values);
                $this->addErrors(self::ERRORS);
            }
        };

        $this->assertFalse($instance->isValid());
        $expect = $instance::ERRORS;
        ksort($expect);
        $this->assertSame($expect, $instance->getErrors());
    }
}
