<?php

namespace sndsgd;

class TypeTest
{
    /**
     * Scalar types and the functions used to verify them
     *
     * @var array<string,string>
     */
    protected static $scalarTypeTests = [
        "bool" => "is_bool",
        "float" => "is_float",
        "int" => "is_int",
        "string" => "is_string",
    ];

    /**
     * Ensure a value is a string or null
     *
     * @param mixed $value The value to test
     * @param string $name The variable/argument name
     * @return string|null
     * @throws \InvalidArgumentException If the value is not a string or null
     */
    public static function nullableString($value, string $name)
    {
        if (!is_string($value) && $value !== null) {
            throw new \InvalidArgumentException(
                "invalid value provided for '$name'; expecting a string or null"
            );
        }
        return $value;
    }

    /**
     * Ensure a value is an int or null
     *
     * @param mixed $value The value to test
     * @param string $name The variable/argument name
     * @return int|null
     * @throws \InvalidArgumentException If the value is not an int or null
     */
    public static function nullableInt($value, string $name)
    {
        if (!is_int($value) && $value !== null) {
            throw new \InvalidArgumentException(
                "invalid value provided for '$name'; expecting an int or null"
            );
        }
        return $value;
    }

    /**
     * Ensure all elements in an array are of a given type
     *
     * @param array $values The value to test
     * @param string $type A scalar type or class name
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function typedArray(array $values, string $type)
    {
        if (!isset(static::$scalarTypeTests[$type])) {
            return self::instanceArray($values, $type);
        }
        return self::scalarArray($values, $type);
    }

    /**
     * Ensure all elements in an array are of a given instance
     *
     * @param array $values
     * @param string $class
     * @return array
     * @throws \InvalidArgumentException
     */
    private static function instanceArray(array $values, string $class)
    {
        foreach ($values as $index => $value) {
            if (!($value instanceof $class)) {
                throw new \InvalidArgumentException(
                   "invalid element at position '$index'; ".
                   "expecting an instance of $class"
                );
            }
        }
        return $values;
    }

    /**
     * Ensure all elements in an array are of a given scalar type
     *
     * @param array $values
     * @param string $type
     * @return array
     * @throws \InvalidArgumentException
     */
    private static function scalarArray(array $values, string $type)
    {
        $func = static::$scalarTypeTests[$type];
        foreach ($values as $index => $value) {
            if (!call_user_func($func, $value)) {
                $article = ($type === "int") ? "an" : "a";
                throw new \InvalidArgumentException(
                   "invalid element at position '$index'; ".
                   "expecting $article $type"
                );
            }
        }
        return $values;
    }
}
