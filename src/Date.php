<?php

namespace sndsgd;

/**
 * Date constants and helpers
 */
class Date
{
    /**
     * @var integer The number of seconds in a minute
     */
    const MINUTE = 60;

    /**
     * @var integer The number of seconds in an hour
     */
    const HOUR = 3600;

    /**
     * @var integer The number of seconds in a day
     */
    const DAY = 86400;

    /**
     * @var integer The number of seconds in a week
     */
    const WEEK = 604800;

    /**
     * Handle microsecond formatting better than `date()`
     *
     * @param float|null $timestamp 
     * @return string
     */
    public static function format(
        float $timestamp = null,
        string $format = "Y-m-d H:i:s.u"
    ): string
    {
        if ($timestamp === null) {
            $timestamp = microtime(true);
        }

        $pos = strpos($format, "u");
        if ($pos !== false) {
            $escpos = $pos - 1;
            if ($escpos < 0 || $format{$escpos} !== "\\") {
                $ms = sprintf("%06d", ($timestamp - floor($timestamp)) * 1000000);
                $format = substr_replace($format, $ms, $pos, 1);
            }
        }

        return date($format, $timestamp);
    }

    /**
     * Create a DateTime instance with microsecond precision
     *
     * @param float|null $timestamp
     * @return \DateTime
     */
    public static function create(float $timestamp = null): \DateTime
    {
        $date = static::format($timestamp);
        return new \DateTime($date);
    }

    /**
     * Create a DateTimeImmutable instance with microsecond precision
     *
     * @param float|null $timestamp
     * @return \DateTime
     */
    public static function createImmutable(
        float $timestamp = null
    ): \DateTimeImmutable
    {
        $date = static::format($timestamp);
        return new \DateTimeImmutable($date);
    }
}
