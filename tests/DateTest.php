<?php

namespace sndsgd;


class DateTest extends \PHPUnit_Framework_TestCase
{
    public function testConstants()
    {
        $min = 60;
        $hour = $min * 60;
        $day = $hour * 24;
        $week = $day * 7;

        $this->assertEquals($min, Date::MINUTE);
        $this->assertEquals($hour, Date::HOUR);
        $this->assertEquals($day, Date::DAY);
        $this->assertEquals($week, Date::WEEK);
    }

    /**
     * @dataProvider providerFormat
     */
    public function testFormat($test, $format, $expect = null)
    {
        if ($expect !== null) {
            $this->assertEquals($expect, Date::format($test, $format));    
        } else {
            $this->assertTrue(is_string(Date::format($test, $format)));
        }
    }

    public function providerFormat()
    {
        $ret = [];
        $fmt = "Y-m-d H:i:s.u";
        $dfmt = "Y-m-d H:i:s";
        $time = time();

        $ret[] = [null, $dfmt];

        # no microtime
        $ret[] = [$time, $fmt, date($dfmt, $time).".000000"];

        # only the microtime
        $ret[] = [.123456, "u", "123456"];

        # should limit precision to six digits
        $mtime = $time + .1234567890;
        $ret[] = [$mtime, $fmt, date($dfmt, $time).".123456"];

        return $ret;
    }

    /**
     * @dataProvider providerCreate
     */
    public function testCreate($test)
    {
        $this->assertInstanceOf("\\DateTime", Date::create($test));
    }

    public function providerCreate()
    {
        return [
            [null],
            [time()],
            [microtime(true)],
        ];
    }
}
