<?php

namespace sndsgd;

/**
 * JSON constants
 */
class Json
{
    /**
     * Encoode options that result in a human readable result
     *  JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
     *
     * @var int
     */
    const HUMAN = 448;

    /**
     * Encoode options that result in a simple result
     *  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
     *
     * @var int
     */
    const SIMPLE = 320;
}
