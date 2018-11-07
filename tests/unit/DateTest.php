<?php

namespace sndsgd;

class DateTest extends \PHPUnit\Framework\TestCase
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
        $this->assertInstanceOf(\DateTime::class, Date::create($test));
    }

    public function providerCreate()
    {
        return [
            [null],
            [time()],
            [microtime(true)],
        ];
    }

    /**
     * @dataProvider providerCreate
     */
    public function testCreateImmutable($test)
    {
        $this->assertInstanceOf(
            \DateTimeImmutable::class,
            Date::createImmutable($test)
        );
    }

    public function providerConvertTimezone()
    {
        $utc = new \DateTimeZone("UTC");

        return [
            [
                new \DateTime("2016-10-13 23:30:00", $utc),
                "America/New_York",
                "UTC",
                false,
                "",
                new \DateTime("2016-10-13 19:30:00"),
            ],
            [
                new \DateTime("2016-10-13 23:30:00", $utc),
                "America/New_York",
                "UTC",
                true,
                "",
                new \DateTime("2016-10-13 19:30:00"),
            ],
            [
                new \stdClass(),
                "America/New_York",
                "UTC",
                true,
                \InvalidArgumentException::class,
                null,
            ],
        ];
    }

    /**
     * @dataProvider providerConvertTimezone
     */
    public function testConvertTimezone(
        $date,
        $to,
        $from,
        $immutable,
        $expectException,
        \DateTime $expect = null
    )
    {
        if ($expectException) {
            $this->expectException($expectException);
        }

        $fmt = "Y-m-d H:i:s";
        $result = Date::convertTimezone($date, $to, $from, $immutable);
        $class = ($immutable) ? \DateTimeImmutable::class : \DateTime::class;
        $this->assertInstanceOf($class, $result);
        $this->assertSame($expect->format($fmt), $result->format($fmt));
    }
}
