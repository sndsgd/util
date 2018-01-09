<?php

namespace sndsgd;

/**
 * Date constants and helpers
 */
class Date
{
    /**
     * @var string A format without a timezone and with microseconds
     */
    const FORMAT = "Y-m-d H:i:s.u";

    /**
     * @var int A single second; useful when trying to improve code readability
     */
    const SECOND = 1;

    /**
     * @var int The number of seconds in a minute
     */
    const MINUTE = 60;

    /**
     * @var int The number of seconds in an hour
     */
    const HOUR = 3600;

    /**
     * @var int The number of seconds in a day
     */
    const DAY = 86400;

    /**
     * @var int The number of seconds in a week
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
        string $format = self::FORMAT
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

    /**
     * Convert a date to another timezone
     *
     * @param $date The date to convert
     * @param string $toTimezone The timezone to conver to
     * @param string $fromTimezone The timezone to convert from
     * @param bool $immutable Whether to return an immuateable instance
     * @return \DateTimeInterface
     */
    public static function convertTimezone(
        $date,
        string $toTimezone,
        string $fromTimezone = "UTC",
        bool $immutable = false
    ): \DateTimeInterface
    {
        if ($date instanceof \DateTimeInterface) {
            $date = $date->format(self::FORMAT);
        } elseif (!is_string($date)) {
            throw new \InvalidArgumentException(
                "invalid value provided for 'date'; expecting an instance of ".
                "DateTimeInterface or a date as string"
            );
        }

        $from = new \DateTimeZone($fromTimezone);
        $to = new \DateTimeZone($toTimezone);

        $ret = ($immutable)
            ? new \DateTimeImmutable($date, $from)
            : new \DateTime($date, $from);

        return $ret->setTimezone($to);
    }
}
