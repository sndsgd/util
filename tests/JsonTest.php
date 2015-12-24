<?php

namespace sndsgd;

/**
 * @coversDefaultClass \sndsgd\Json
 */
class JsonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @coversNothing
     */
    public function testConstant()
    {
        $expect = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $this->assertEquals($expect, Json::HUMAN);
    }
}
