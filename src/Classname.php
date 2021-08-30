<?php

namespace sndsgd;

/**
 * Classname utility methods
 */
class Classname
{
    // regex used to trim excess junk off possible classname strings
    const REGEX_TRIM = "/(^[^a-z0-9_]+)|([^a-z0-9_]+$)/i";
    // regex used to split strings into namespace and classname parts
    const REGEX_SPLIT = "/([^a-z0-9_])+/i";

    protected $classname;
    protected $lastDelimPosition;

    public function __construct(string $classname)
    {
        $this->classname = trim($classname, "\\");
        $this->lastDelimPosition = strrpos($this->classname, "\\");
    }

    public function __toString()
    {
        return "\\".$this->classname;
    }

    public function getClass(): string
    {
        return ($this->lastDelimPosition)
            ? substr($this->classname, $this->lastDelimPosition + 1)
            : $this->classname;
    }

    public function getNamespace(): string
    {
        return ($this->lastDelimPosition)
            ? substr($this->classname, 0, $this->lastDelimPosition)
            : "";
    }

    /**
     * Split a string into namespace and classname sections
     *
     * @param string $class
     * @return array<string>
     */
    public static function split(string $class): array
    {
        $class = preg_replace(self::REGEX_TRIM, "", $class);
        return preg_split(self::REGEX_SPLIT, $class);
    }

    /**
     * Convert a string into a namespaced classname
     *
     * @param string|array<string> $class
     * @return string
     */
    public static function toString($class, $separator = "\\"): string
    {
        if (is_string($class)) {
            $class = self::split($class);
        }
        return implode($separator, $class);
    }

    /**
     * Convert a string into a namespaced method name
     *
     * @param string|array<string> $class
     * @param boolean $asArray Whether or not to return as an array
     * @return string|array<string>
     */
    public static function toMethod($class, $asArray = false)
    {
        if (is_string($class)) {
            $class = self::split($class);
        }
        $method = array_pop($class);
        $classname = implode("\\", $class);
        return ($asArray) ? [$classname, $method] : "$classname::$method";
    }

    /**
     * Get a namespaced classname from a string of php
     *
     * @param string $contents
     * @return string
     */
    public static function fromContents(string $contents): string
    {
        $class = "";
        $namespace = "";
        $tokens = token_get_all($contents);
        $len = count($tokens);

        for ($i = 0; $i < $len; $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                for ($j = $i + 1; $j < $len; $j++) {
                    if (
                        $tokens[$j][0] === T_STRING
                        || $tokens[$j][0] === T_NAME_QUALIFIED
                    ) {
                        $namespace .= "\\".$tokens[$j][1];
                    }
                    else if ($tokens[$j] === "{" || $tokens[$j] === ";") {
                        break;
                    }
                }
            }
            else if ($tokens[$i][0] === T_CLASS) {
                for ($j = $i+1; $j < $len; $j++) {
                    if ($tokens[$j][0] === T_STRING) {
                        $class = $tokens[$j][1];
                        break 2;
                    }
                }
            }
        }

        if ($class === "") {
            return "";
        }
        return ($namespace === "")
            ? $class
            : trim($namespace."\\".$class, "\\");
    }
}
