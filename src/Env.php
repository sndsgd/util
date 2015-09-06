<?php

namespace sndsgd;

use \Exception;
use \InvalidArgumentException;
use \sndsgd\env\Controller;


class Env
{
    // the various levels of verbosity
    const QUIET = -1;
    const NORMAL = 0;
    const V = 1;
    const VV = 2;
    const VVV = 3;

    /**
     * The current controller instance
     *
     * @var \sndsgd\env\Controller
     */
    private static $controller = null;

    /**
     * The max level of verbosity to write
     *
     * @var integer
     */
    private static $verboseLevel = 0;

    /**
     * Set the current controller instance
     *
     * @param \sndsgd\env\Controller|null $controller
     * @return void
     */
    public static function setController(Controller $controller = null)
    {
        self::$controller = $controller;
    }

    /**
     * Get the current controller instance
     *
     * @return \sndsgd\env\Controller|null
     */
    public static function getController()
    {
        return self::$controller;
    }

    private static function validateVerboseLevel($level, $min = self::QUIET)
    {
        if (
            !is_int($level) ||
            $level < $min ||
            $level > self::VVV
        ) {
            throw new InvalidArgumentException(
                "invalid value provided for 'level'; expecting Env::NORMAL, ".
                "Env::V, Env::VV, or Env::VVV"
            );
        }
    }

    /**
     * Set the verbose level
     *
     * @param integer $level
     * @return void
     */
    public static function setVerboseLevel($level)
    {
        self::validateVerboseLevel($level);
        self::$verboseLevel = $level;
    }

    /**
     * Get the current verbose level
     *
     * @return integer
     */
    public static function getVerboseLevel()
    {
        return self::$verboseLevel;
    }

    /**
     * Validate a message that is passed to ::log() or ::error()
     *
     * @param string|callable $message
     * @return string
     * @throws \InvalidArgumentException If the message is not a string
     */
    private static function validateMessage($message)
    {
        if (is_callable($message)) {
            $message = $message();
        }

        if (!is_string($message)) {
            throw new InvalidArgumentException(
                "invalid value provided for 'message'; expecting a string OR ".
                "a callable that returns a string"
            );
        }

        return $message;
    }

    /**
     * Write an info env message
     *
     * Info message are only written in a controller instance has been defined
     * @param string $message The message to write
     * @param integer $level The verbose level for the message
     * @return void
     */
    public static function log($message, $level = self::NORMAL)
    {
        self::validateVerboseLevel($level, self::NORMAL);

        if (
            self::$controller !== null &&
            self::$verboseLevel > self::QUIET &&
            $level <= self::$verboseLevel
        ) {
            $message = self::validateMessage($message);
            self::$controller->log($message);
        }
    }

    /**
     * Write an error env message
     *
     * If no controller has been defined, a fatal error will be triggered
     * @param string $message The message to write
     * @param integer|null $exitcode The exitcode to pass to `exit()`
     * @return void
     */
    public static function error($message, $exitcode = 1)
    {
        $message = self::validateMessage($message);
        if (self::$controller !== null) {
            self::$controller->error($message);
        }

        if ($exitcode) {
            self::terminate($exitcode);
        }
    }

    /**
     * Kill the script
     *
     * @param integer $exitcode
     * @return void
     */
    public static function terminate($exitcode)
    {
        if (self::$controller === null) {
            exit($exitcode);
        }
        self::$controller->terminate($exitcode);
    }
}
