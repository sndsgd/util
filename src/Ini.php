<?php

namespace sndsgd;

/**
 * Class for normalizing the results of calls to `ini_get()`
 */
class Ini
{
    /**
     * Once values are retrieved they are cached here
     *
     * @var array<string,mixed>
     */
    protected $values = [];

    /**
     * Retrieve the `upload_max_filesize` ini setting
     *
     * @return int The max upload filesize in bytes
     */
    public function getMaxUploadFileSize(): int
    {
	if (!isset($this->values["upload_max_filesize"])) {
	    $value = ini_get("upload_max_filesize");
	    $this->values["upload_max_filesize"] = $this->convertToBytes($value);
	}

	return $this->values["upload_max_filesize"];
    }

    /**
     * Retrieve the `memory_limit` ini setting
     *
     * @return int The memory limit in bytes
     */
    public function getMemoryLimit(): int
    {
	if (!isset($this->values["memory_limit"])) {
	    $value = ini_get("memory_limit");
	    $this->values["memory_limit"] = $this->convertToBytes($value);
	}

	return $this->values["memory_limit"];
    }

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
}
