<?php

namespace sndsgd;

class SometimesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideIsEnabledBounds
     */
    public function testIsEnabledBounds($percent, $expect)
    {
        $sometimes = new \sndsgd\Sometimes();
        $this->assertSame($expect, $sometimes->isEnabled($percent));
    }

    public function provideIsEnabledBounds(): array
    {
        return [
            [-1, false],
            [0, false],
            [100, true],
            [101, true],
        ];
    }

    /**
     * @dataProvider provideIsEnabledPercentOnly
     */
    public function testIsEnabledPercentOnly($percent, $expectMin, $expectMax)
    {
        # the expected bounds are too aggressive for all tests, but indicate
        # the expected results, so try a few times before really failing
        for ($i = 0; $i < 5; $i++) {
            $result = $this->execPercentTest($percent);
            if ($result >= $expectMin && $result <= $expectMax) {
                break;
            }
        }

        $this->assertGreaterThanOrEqual($expectMin, $result);
        $this->assertLessThanOrEqual($expectMax, $result);
    }

    public function provideIsEnabledPercentOnly(): array
    {
        return [
            [0.01, 0.005, 0.015],
            [0.1, 0.05, 0.15],
            [0.25, 0.2, 0.3],
            [0.5, 0.45, 0.55],
            [1.0, 0.05, 1.5],
            [2.0, 1.5, 2.5],
            [5.0, 4.5, 5.5],
        ];
    }

    private function execPercentTest($percent)
    {
        $loops = 10000;
        $on = 0;
        $sometimes = new \sndsgd\Sometimes();
        for ($i = 0; $i < $loops; $i++) {
            if ($sometimes->isEnabled($percent)) {
                $on++;
            }
        }

        return $on / $loops * 100;
    }

    /**
     * @dataProvider provideIsEnabledConsistent
     */
    public function testIsEnabledConsistent($percent, $value, $salt, $expect)
    {
        $sometimes = new \sndsgd\Sometimes();
        $this->assertSame($expect, $sometimes->isEnabled($percent, $value, $salt));
    }

    public function provideIsEnabledConsistent(): array
    {
        $ret = [];

        # this combo should be enabled from .01 and up
        $value = "kWjvi2Y2gwqeUi2FIlhqHqzDJAx2RvWHjd0o1Eaird";
        $salt = "JZFecfzmwoPIliPgmb1EHYWHL6PpIUh3BBvryNSYAn";

        for ($i = .01; $i < .1; $i += .01) {
            $ret[] = [$i, $value, $salt, true];
        }

        for ($i = .1; $i < 1; $i += .1) {
            $ret[] = [$i, $value, $salt, true];
        }

        for ($i = 0; $i < 25; $i++) {
            $ret[] = [mt_rand(1, 99), $value, $salt, true];
        }

        # this combo should be enabled from 50 and up
        $value = "VKM2IDsWaIAr3duXErYYxQPkDTUDB45xyjbRvMnJJp";
        $salt = "h3TIEt2hvUTbkH8qEjYcQyiyflZRNiB6YrfwuYgfro";

        for ($i = .1; $i < 1; $i += .1) {
            $ret[] = [$i, $value, $salt, false];
        }

        for ($i = 1; $i < 50; $i++) {
            $ret[] = [$i, $value, $salt, false];
        }

        for ($i = 50; $i <= 100; $i++) {
            $ret[] = [$i, $value, $salt, true];
        }

        return $ret;
    }
}
