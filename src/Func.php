<?php

namespace sndsgd;

/**
 * Function and method utility methods
 */
class Func
{
    /**
     * Verify a callable provided as a string
     *
     * @param string $func The callable to verify
     * @return bool
     */
    public static function exists(string $func): bool
    {
        if (strpos($func, "::") !== false) {
            list($class, $method) = explode("::", $func);
            return method_exists($class, $method);
        }
        return function_exists($func);
    }

    /**
     * Get a reflection object for a function or static method
     *
     * @param callable $func The function to reflect
     * @return \ReflectionFunctionAbstract
     * @throws \ReflectionException If `$func` does not exist
     */
    public static function getReflection(callable $func): \ReflectionFunctionAbstract
    {
        if (is_string($func) && strpos($func, "::") !== false) {
            list($classname, $method) = explode("::", $func, 2);
            $rc = new \ReflectionClass($classname);
            return $rc->getMethod($method);
        }
        return new \ReflectionFunction($func);
    }
}
