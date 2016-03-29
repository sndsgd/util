<?php

namespace sndsgd;

class TypeTest
{
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
}
