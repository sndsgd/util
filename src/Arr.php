<?php

namespace sndsgd;

/**
 * Array utility methods
 */
class Arr
{
    /**
     * Determine whether or not an array is indexed
     *
     * @param array $test The array to test
     * @return boolean
     */
    public static function isIndexed(array $test)
    {
        return (array_values($test) === $test);
    }

    /**
     * Convert a variable into the first element of an array
     *
     * Note: this is useful when $value is NULL
     * <code>
     * var_dump((array) null);
     * // => array(0)
     * </code>
     *
     * @param mixed $value The value to convert to an array
     * @return array
     */
    public static function cast($value): array
    {
        return (is_array($value)) ? $value : [$value];
    }

    /**
     * Add a value to an array
     *
     * @param array $arr The array to add values to
     * @param string|number $key The index/key to add the value under
     * @param string|number $value The value to add
     */
    public static function addValue(array &$arr, $key, $value)
    {
        if (isset($arr[$key])) {
            if (!is_array($arr[$key])) {
                $arr[$key] = [$arr[$key]];
            }
            $arr[$key][] = $value;
        }
        else {
            $arr[$key] = $value;
        }
    }

    /**
     * Get an array value, or use a default if the key doesn't exist
     *
     * @param array $arr
     * @param string|integer $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(array $arr, $key, $default = null)
    {
        return isset($arr[$key]) ? $arr[$key] : $default;
    }

    /**
     * Ensure that keys exist in an array
     * @todo this 'bool|string' return is wonky
     *
     * @param array $arr The array to test
     * @param array $keys Keys to check for in $test
     * @return boolean|string
     * @return boolean TRUE if all required keys exist
     * @return string An error message indicating a key does NOT exist
     */
    public static function requireKeys(array $arr, array $keys)
    {
        foreach ($keys as $key) {
            if (!isset($arr[$key])) {
                return "missing required key '$key'";
            }
        }
        return true;
    }

    /**
     * Ensure that all keys in one array exist in another
     *
     * @param array $arr The array to fill with missing values
     * @param array $defaults The values to fill $arr with
     * @return array
     */
    public static function defaults(array $arr = null, array $defaults): array
    {
        if ($arr === null) {
            return $defaults;
        }
        foreach ($defaults as $key => $value) {
            if (!isset($arr[$key])) {
                $arr[$key] = $value;
            }
        }
        return $arr;
    }

    /**
     * Flatten an array
     *
     * @param array $arr The array to flatten
     * @return array
     */
    public static function flatten(array $arr): array
    {
        $i = 0;
        $len = count($arr);
        for (; $i<$len; $i++) {
            if (is_array($arr[$i])) {
                array_splice($arr, $i, 1, $arr[$i]);
                $len--;
            }
        }
        return $arr;
    }

    /**
     * Implode values with a defferent delimeter before the last element
     *
     * @param string $delim The delimeter
     * @param array $arr The array to implode
     * @param string|null $beforeLast A string to prepend to the last element
     * @return string
     */
    public static function implode($delim, array $arr, $beforeLast = null): string
    {
        if (count($arr) > 1 && $beforeLast !== null) {
            $arr[] = $beforeLast.array_pop($arr);
        }
        return implode($delim, $arr);
    }

    /**
     * Selectively remove values from an array based on their keys
     *
     * @param array $arr The array to operate on
     * @param string ...$keys The key(s) to remove from the array
     * @return array
     */
    public static function without(array $arr, string ...$keys): array
    {
        foreach ($keys as $key) {
            if (isset($arr[$key])) {
                unset($arr[$key]);
            }
        }
        return $arr;
    }

    /**
     * Selectively keep values in an array based on their keys
     *
     * @param array $arr The array to operate on
     * @param string ...$keys The key(s) to remove from the array
     * @return array
     */
    public static function only(array $arr, string ...$keys): array
    {
        $ret = [];
        foreach ($keys as $key) {
            if (isset($arr[$key])) {
                $ret[$key] = $arr[$key];
            }
        }
        return $ret;
    }

    /**
     * Filter an array with a callback that can analyze keys AND values
     *
     * @param array $arr The array to operate on
     * @param callable $fn The callback function
     * @return array
     */
    public static function filter(array $arr, callable $fn): array
    {
        return array_filter($arr, $fn, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Remove values from the end of an array using comparison
     *
     * @param array $arr The source array
     * @param mixed $match Values matching this value will be removed
     * @param boolean $strict Whether or not to use strict comparison
     * @return array
     */
    public static function popValues(
        array $arr,
        $match = false,
        bool $strict = false
    ): array
    {
        $func = Compare::getMethod($strict);
        $len = count($arr);
        while ($len > 0 && call_user_func($func, end($arr), $match)) {
            array_pop($arr);
            $len--;
        }
        return $arr;
    }

    /**
     * Remove values from the beginning of an array using comparison
     *
     * @param array $arr The source array
     * @param mixed $match Values matching this value will be removed
     * @param boolean $strict Whether or not to use strict comparison
     * @return array
     */
    public static function shiftValues(
        array $arr,
        $match = false,
        bool $strict = false
    ): array
    {
        $func = Compare::getMethod($strict);
        $len = count($arr);
        while ($len > 0 && call_user_func($func, reset($arr), $match)) {
            array_shift($arr);
            $len--;
        }
        return $arr;
    }

    /**
     * Create an attribute string from an associative array
     *
     * @param array<string,string|integer|float>|null $arr
     * @return string
     */
    public static function toAttributeString(array $arr = null): string
    {
        $ret = "";
        if ($arr !== null) {
            foreach ($arr as $k => $v) {
                $ret .= " $k=\"$v\"";
            }
        }
        return $ret;
    }

    /**
     * Test that a given key exists, and its value passes a test function
     *
     * @param array $arr The array to search
     * @param string $key The key of the array value to test
     * @param callable $test A function to test the value
     * @return boolean
     */
    public static function testValueByKey(
        array $arr,
        string $key,
        callable $test
    ): bool
    {
        return (array_key_exists($key, $arr) && $test($arr[$key]));
    }

    /**
     * Export an array as a string with better than default formatting
     *
     * @param array $arr The array to export
     * @return string
     */
    public static function export(array $arr): string
    {
        $str = var_export($arr, true);
        $str = str_replace("array (", "array(", $str);
        $str = preg_replace("/=>(\s+)array\(/s", "=> array(", $str);
        $str = preg_replace("/array\(\s+\)/", "array()", $str);
        return $str;
    }
}
