<?php

namespace sndsgd;

class IniTest extends \PHPUnit_Framework_TestCase
{
    use \phpmock\phpunit\PHPMock;

    /**
     * @dataProvider provideConvertToBool
     */
    public function testConvertToBool(string $iniValue, bool $expect)
    {
	$iniGetMock = $this->getFunctionMock(__NAMESPACE__, "ini_get");
	$iniGetMock->expects($this->any())->willReturn($iniValue);

	$ini = new Ini();
	$this->assertSame($expect, $ini->get("enable_post_data_reading"));
	$this->assertSame($expect, $ini->get("enable_post_data_reading"));
    }

    public function provideConvertToBool(): array
    {
	return [
	    ['on', true],
	    ['On', true],
	    ['off', false],
	    ['Off', false],
	    ['true', true],
	    ['TrUe', true],
	    ['FaLsE', false],
	];
    }

    /**
     * @dataProvider provideConvertToInt
     */
    public function testConvertToInt(string $iniValue, int $expect)
    {
	$iniGetMock = $this->getFunctionMock(__NAMESPACE__, "ini_get");
	$iniGetMock->expects($this->any())->willReturn($iniValue);

	$ini = new Ini();
	$this->assertSame($expect, $ini->get("max_input_vars"));
	$this->assertSame($expect, $ini->get("max_input_vars"));
    }

    public function provideConvertToInt(): array
    {
	return [
	    ['0', 0],
	    ['1', 1],
	    ['42', 42],
	    [(string) PHP_INT_MAX, PHP_INT_MAX],
	];
    }

    /**
     * @dataProvider provideConvertToBytes
     */
    public function testConvertToBytes(string $iniValue, int $expect)
    {
	$iniGetMock = $this->getFunctionMock(__NAMESPACE__, "ini_get");
	$iniGetMock->expects($this->any())->willReturn($iniValue);

	$ini = new Ini();
	$this->assertSame($expect, $ini->get("memory_limit"));
	$this->assertSame($expect, $ini->get("memory_limit"));
    }

    /**
     * Data provider for methods that convert human readable sizes into bytes
     */
    public function provideConvertToBytes(): array
    {
	return [
	    ["128M", 134217728],
	    ["128m", 134217728],
	    ["42M", 44040192],
	    ["42K", 43008],
	];
    }
}
