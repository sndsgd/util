<?php declare(strict_types=1);

namespace sndsgd;

use LogicException;

class VariableUtil
{
    /**
     * Export a variable for use in generated code
     *
     * @param mixed $value The variable to export
     * @param int $arrayNestingDepth The depth for indenting nested arrays
     * @return string
     */
    public static function export(
        $value,
        int $arrayNestingDepth = 0,
    ): string {
        if (is_null($value)) {
            return "null";
        }

        if (is_scalar($value)) {
            return var_export($value, true);
        }

        if (is_array($value)) {
            if ($value === []) {
                return "[]";
            }

            return self::exportArrayRecursive($value, $arrayNestingDepth);
        }

        throw new LogicException(
            sprintf(
                "support for %s values is not implemented in %s",
                gettype($value),
                self::class,
            ),
        );
    }

    /**
     * Rescursively format an array so it conforms to modern coding standards
     *
     * Note: this is not implemented in a such a way to handle objects!
     *
     * @param array $value The value to format
     * @param int $depth The number of indent levels
     * @param int $indentStep The number of spaces per indent
     * @return string
     */
    private static function exportArrayRecursive(
        array $value,
        int $depth = 0,
        int $indentStep = 4
    ): string {
        $indentChars = $depth * $indentStep;
        $indentStr = str_repeat(" ", $indentChars);
        $nestedIndentStr = str_repeat(" ", $indentChars + $indentStep);

        $ret = "[\n";

        // if the value is indexed, we don't want to include the numeric keys
        $include_keys = array_values($value) !== $value;

        $keys = array_keys($value);
        for ($i = 0, $len = count($keys); $i < $len; $i++) {
            $key = $keys[$i];
            $val = $value[$key];

            if ($i !== 0) {
                $ret .= ",\n";
            }

            $val = self::export($val, $depth + 1);
            if ($include_keys) {
                $ret .= sprintf("%s'%s' => %s", $nestedIndentStr, $key, $val);
            } else {
                $ret .= sprintf("%s%s", $nestedIndentStr, $val);
            }
        }

        $ret .= ",\n";
        $ret .= $indentStr . "]";
        return $ret;
    }
}