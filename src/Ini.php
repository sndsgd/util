<?php

namespace sndsgd;

/**
 * Class for normalizing the results of calls to `ini_get()`
 * No plans to update this for custom ini files
 */
class Ini
{
    const CONVERT_TO_BOOL = 1;
    const CONVERT_TO_INT = 2;
    const CONVERT_TO_BYTES = 3;

    /**
     * A map of values and how they should be converted
     *
     * @var array<string,int>
     */
    const CONVERSIONS = [
        "enable_post_data_reading" => self::CONVERT_TO_BOOL,
        "max_input_nesting_level" => self::CONVERT_TO_INT,
        "max_input_vars" => self::CONVERT_TO_INT,
        "memory_limit" => self::CONVERT_TO_BYTES,
        "upload_max_filesize" => self::CONVERT_TO_BYTES,
    ];

    /**
     * Convert a size string into a bytes integer
     *
     * @param string $value The size string to convert
     * @return int
     */
    public static function convertToBytes(string $value): int
    {
        $units = "BKMGT";
        $unit = preg_replace("/[^$units]/i", "", $value);
        $value = floatval($value);
        if ($unit) {
            $value *= pow(1024, stripos($units, $unit[0]));
        }

        return (int) $value;
    }

    /**
     * Once values are retrieved they are cached here
     *
     * @var array<string,mixed>
     */
    protected $values = [];

    /**
     * Retrieve an ini value
     *
     * @param string $key The value to retrieve
     * @return mixed The normalized result
     */
    public function get(string $key)
    {
        if (!isset($this->values[$key])) {
            $value = ini_get($key);

            switch (self::CONVERSIONS[$key] ?? 0) {
                case self::CONVERT_TO_BOOL:
                    $value = Str::toBoolean($value);
                    break;
                case self::CONVERT_TO_INT:
                    $value = (int) $value;
                    break;
                case self::CONVERT_TO_BYTES:
                    $value = self::convertToBytes($value);
                    break;
            }

            $this->values[$key] = $value;
        }

        return $this->values[$key];
    }
}
