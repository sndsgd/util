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

    public function testCreate()
    {
        $dt1 = Date::create();
        $this->assertInstanceOf("DateTime", $dt1);

        $ts = microtime(true);
        $dt2 = Date::create($ts);
        $this->assertInstanceOf("DateTime", $dt2);
    }
}