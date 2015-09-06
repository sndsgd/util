<?php

namespace sndsgd\env;

use \sndsgd\Env;
use \sndsgd\Str;


/**
 * A env controller
 */
class Controller
{
    /**
     * Style definition pattern
     *
     * @var string
     */
    const STYLE_REGEX = '/@\\[([a-z-:+ ]+)\\]/';

    /**
     * Available style codes
     *
     * The values should be replaced with values that are relevant to the
     * controller environment
     * @var array<string,mixed>
     */
    protected $styleCodes = [

        # reset
        'reset' => null,
        'reset-bold' => null,
        'reset-dim' => null,
        'reset-underline' => null,
        'reset-blink' => null,
        'reset-reverse' => null,
        'reset-hidden' => null,
        'reset-fg' => null,
        'reset-bg' => null,

        # style
        'bold' => null,
        'dim' => null,
        'underline' => null,
        'blink' => null,
        'reverse' => null,
        'hidden' => null,

        # foreground
        'default' => null,
        'fg:' => null,
        'black' => null,
        'red' => null,
        'green' => null,
        'yellow' => null,
        'blue' => null,
        'magenta' => null,
        'cyan' => null,
        'light-gray' => null,
        'dark-gray' => null,
        'light-red' => null,
        'light-green' => null,
        'light-yellow' => null,
        'light-blue' => null,
        'light-magenta' => null,
        'light-cyan' => null,
        'white' => null,

        # background
        'bg:default' => null,
        'bg:' => null,
        'bg:black' => null,
        'bg:red' => null,
        'bg:green' => null,
        'bg:yellow' => null,
        'bg:blue' => null,
        'bg:magenta' => null,
        'bg:cyan' => null,
        'bg:light-gray' => null,
        'bg:dark-gray' => null,
        'bg:light-red' => null,
        'bg:light-green' => null,
        'bg:light-yellow' => null,
        'bg:light-blue' => null,
        'bg:light-magenta' => null,
        'bg:light-cyan' => null,
        'bg:white' => null,
    ];

    /**
     * Whether or not to include colors in formatted output
     *
     * @var boolean
     */
    protected $showStyles = true;

    /**
     * Enable styles in messages
     *
     * @return void
     */
    public function enableStyles()
    {
        $this->showStyles = true;
    }

    /**
     * Disable styles in messages
     *
     * @return void
     */
    public function disableStyles()
    {
        $this->showStyles = false;
    }

    /**
     * Find all style definition blocks
     *
     * @param string $message The message content
     * @return array<string,array<string>>
     */
    protected function extractStyles($message)
    {
        $ret = [];
        if (preg_match_all(self::STYLE_REGEX, $message, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $captures) {
                $match = $captures[0];
                $keys = explode('+', $captures[1]);
                $ret[$match] = array_map('trim', $keys);
            }
        }
        return $ret;
    }

    /**
     * Given the results of extractStyles(), apply styles
     *
     * @param array<string,mixed> $map
     * @param string $message The message contents
     * @return string
     */
    protected function applyStyles(array $map, $message)
    {
        foreach ($map as $match => $codes) {
            $message = str_replace($match, '', $message);
        }
        return $message;
    }

    /**
     * Write a message
     *
     * @param string $message
     * @return void
     */
    public function write($message)
    {
        $map = $this->extractStyles($message);
        $message = $this->applyStyles($map, $message);
        if (Str::endsWith($message, PHP_EOL) === false) {
            $message .= PHP_EOL;
        }
        echo $message;
    }

    /**
     * Write env info
     *
     * @param string $message The message to write
     */
    public function log($message)
    {
        $this->write($message);
    }

    /**
     * Write an error message
     *
     * @param string $message The message to write
     */
    public function error($message)
    {
        $this->write($message);
    }

    /**
     * Kill the script
     *
     * @param integer $exitcode
     * @return void
     */
    public function terminate($exitcode)
    {
        exit($exitcode);
    }
}
