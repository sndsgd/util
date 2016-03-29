<?php

namespace sndsgd;

/**
 * Comparison utility methods
 */
class Compare
{
    /**
     * Determine if values are equal
     *
     * @param mixed $a
     * @param mixed $b
     * @return boolean
     */
    public static function equal($a, $b): bool
    {
        return ($a == $b);
    }

    /**
     * Determine if values are identical
     *
     * @param mixed $a
     * @param mixed $b
     * @return boolean
     */
    public static function strictEqual($a, $b): bool
    {
        return ($a === $b);
    }

    /**
     * Get comparison method name
     *
     * @param boolean $strict
     * @return string
     */
    public static function getMethod(bool $strict = false): string
    {
        return ($strict === false)
            ? "sndsgd\\Compare::equal"
            : "sndsgd\\Compare::strictEqual";
    }
}
