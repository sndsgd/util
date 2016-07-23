<?php

namespace sndsgd;

/**
 * A utility for tracking code execution durations
 */
class Timer
{
    /**
     * All timers that are given a name are referenced here
     *
     * @var array<string,Timer>
     */
    private static $timers = [];

    /**
     * Reset the array of referenced timers
     *
     * @return void
     */
    public static function reset()
    {
        self::$timers = [];
    }

    /**
     * Get all durations for named timers
     *
     * @param int $precision
     * @return array<string,string|float|integer>
     */
    public static function getDurations(int $precision = -1): array
    {
        $ret = [];
        foreach (self::$timers as $t) {
            Arr::addValue($ret, $t->getName(), $t->getDuration($precision));
        }
        return $ret;
    }

    /**
     * A nickname for the timer
     *
     * @var string|null
     */
    protected $name;

    /**
     * The start time in microseconds
     *
     * @var float|null
     */
    protected $startTime;

    /**
     * The start time in microseconds
     *
     * @var float|null
     */
    protected $stopTime;

    /**
     * The duration in microseconds
     *
     * @var float|null
     */
    protected $duration;

    /**
     * @param string|null $name A handle to give the timer instance
     * @param float $startTime An optional time to start the timer
     */
    public function __construct(string $name = null, float $startTime = 0.0)
    {
        if ($name !== null) {
            self::$timers[] = $this; 
        }
        $this->name = $name;
        $this->startTime = ($startTime !== 0.0) ? $startTime : microtime(true);
    }

    /**
     * Convert the object into a string
     *
     * @return string
     */
    public function __toString()
    {
        $name = $this->getName();
        $time = $this->getDuration(5);
        return ($this->getStopTime() === null)
            ? "{$name} has consumed {$time} seconds so far"
            : "{$name} took {$time} seconds";
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? "unknown";
    }

    /**
     * Get the start time
     *
     * @return float
     */
    public function getStartTime(): float
    {
        return $this->startTime;
    }

    /**
     * Start the timer
     *
     * @return void
     */
    public function restart()
    {
        $this->startTime = microtime(true);
    }

    /**
     * Stop the timer and calculate the duration
     *
     * @return float|string The timer duration
     */
    public function stop(int $precision = -1)
    {
        $time = microtime(true);
        if ($this->stopTime === null) {
            $this->stopTime = $time;
            $this->duration = $this->stopTime - $this->startTime;
        }
        return $this->fmtDuration($this->duration, $precision);
    }

    /**
     * Get the stop time
     *
     * @return float|null
     */
    public function getStopTime()
    {
        return $this->stopTime;
    }

    /**
     * Get the current duration
     *
     * @param integer $precision
     * @return string|float
     */
    public function getDuration(int $precision = -1)
    {
        //var_dump($this->duration);
        $duration = $this->duration ?? microtime(true) - $this->startTime;
        return $this->fmtDuration($duration, $precision);
    }

    /**
     * @return string|float
     */
    private function fmtDuration(float $duration, int $precision)
    {
        return ($precision < 0) 
            ? $duration
            : number_format($duration, $precision);
    }
}
