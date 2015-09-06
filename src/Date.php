<?php

namespace sndsgd;

use \DateTime;


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
     * Create a DateTime instance with microsecond precision
     *
     * @param integer|float|null $timestamp
     * @return \DateTime
     */
    public static function create($timestamp = null)
    {
        if ($timestamp === null) {
            $timestamp = microtime(true);
        }

        $ms = sprintf("%06d", ($timestamp - floor($timestamp)) * 1000000);
        return new DateTime(date("Y-m-d H:i:s.$ms", $timestamp));
    }
}
