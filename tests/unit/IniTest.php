<?php

namespace sndsgd;

class IniTest extends \PHPUnit_Framework_TestCase
{
    use \phpmock\phpunit\PHPMock;

    /**
     * Data provider for methods that convert human readable sizes into bytes
     */
    public function provideByteSizes(): array
    {
	return [
	    ["128M", 134217728],
	    ["128m", 134217728],
	    ["42M", 44040192],
	    ["42K", 43008],
	];
    }

    /**
     * @dataProvider provideByteSizes
     */
    public function testGetMaxUploadFileSize(string $iniValue, int $expect)
    {
	$iniGetMock = $this->getFunctionMock(__NAMESPACE__, "ini_get");
	$iniGetMock->expects($this->any())->willReturn($iniValue);

	# call it twice to test memoization
	$ini = new Ini();
	$this->assertSame($expect, $ini->getMaxUploadFileSize());
	$this->assertSame($expect, $ini->getMaxUploadFileSize());
    }

    /**
     * @dataProvider provideByteSizes
     */
    public function testGetMemoryLimit(string $iniValue, int $expect)
    {
	$iniGetMock = $this->getFunctionMock(__NAMESPACE__, "ini_get");
	$iniGetMock->expects($this->any())->willReturn($iniValue);

	# call it twice to test memoization
	$ini = new Ini();
	$this->assertSame($expect, $ini->getMemoryLimit());
	$this->assertSame($expect, $ini->getMemoryLimit());
    }
}
