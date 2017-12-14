<?php

namespace sndsgd;

class Sometimes
{
    /**
     * Determine whether something is 'on' for a desired percentage of calls
     *
     * @param float $percentage The percentage value ranging from 0 to 100
     * @param string $value An optional value to produce consistent results
     * @param string $salt An optional salt for even distribution across values
     * @return bool
     */
    public function isEnabled(
        float $percentage,
        string $value = "",
        string $salt = ""
    ): bool
    {
        if ($percentage <= 0) {
            return false;
        }

        if ($percentage >= 100) {
            return true;
        }

        if ($value === "" && $salt === "") {
            $max = mt_getrandmax() + 1;
            $random = mt_rand() / $max;
            return ($random < $percentage / 100);
        }

        $hash = crc32($value.$salt);
        $mod = $hash % 10000;
        return ($mod < $percentage * 100);
    }
}
