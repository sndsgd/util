<?php

namespace sndsgd;

class NetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider providePing
     */
    public function testPing($host, $port, $timeout, $expect)
    {
        $this->assertSame($expect, Net::ping($host, $port, $timeout));
    }

    public function providePing()
    {
        return [
            ["google.com", 443, 10, true],
            ["999.999.999.999", 80, 1, false],
        ];
    }

    /**
     * @dataProvider provideParseCidrMissingDelimeterException
     */
    public function testParseCidrMissingDelimeterException($cidr)
    {
       $this->expectException(\UnexpectedValueException::class);
       $this->expectExceptionMessage("invalid CIDR; missing expected '/' delimeter");
       Net::parseCidr($cidr);
    }

    public function provideParseCidrMissingDelimeterException(): array
    {
        return [
            [""],
            ["nope"],
            ["111.222.111.222"],
            ["32"],
        ];
    }

    /**
     * @dataProvider provideParseCidrMissingIpException
     */
    public function testParseCidrMissingIpException($cidr)
    {
       $this->expectException(\UnexpectedValueException::class);
       $this->expectExceptionMessage("invalid CIDR; missing expected ip");
       Net::parseCidr($cidr);
    }

    public function provideParseCidrMissingIpException(): array
    {
        return [
            ["/"],
            ["/8"],
            ["/32"],
        ];
    }

    /**
     * @dataProvider provideParseCidrInvalidIpException
     */
    public function testParseCidrInvalidIpException($cidr)
    {
       $this->expectException(\UnexpectedValueException::class);
       $this->expectExceptionMessage("is not a valid IPv4 address");
       Net::parseCidr($cidr);
    }

    public function provideParseCidrInvalidIpException(): array
    {
        return [
            ["123/"],
            ["nope/"],
            ["256.256.256.256/"],
        ];
    }

    /**
     * @dataProvider provideParseCidrInvalidNetmaskException
     */
    public function testParseCidrInvalidNetmaskException($cidr)
    {
       $this->expectException(\UnexpectedValueException::class);
       $this->expectExceptionMessage("network mask; expecting a value between 0 and 32");
       Net::parseCidr($cidr);
    }

    public function provideParseCidrInvalidNetmaskException(): array
    {
        return [
            ["111.222.111.222/-1"],
            ["111.222.111.222/33"],
            ["111.222.111.222/42"],
        ];
    }

    /**
     * @dataProvider provideIsIpInCidrIpException
     */
    public function testIsIpInCidrIpException($ip)
    {
       $this->expectException(\UnexpectedValueException::class);
       $this->expectExceptionMessage("invalid value for ip; expecting an IPv4 address");
       Net::isIpInCidr($ip, "");
    }

    public function provideIsIpInCidrIpException()
    {
        return [
            ["nope"],
            ["256.256.256.256"],
        ];
    }

    /**
     * @dataProvider provideIsIpInCidr
     */
    public function testIsIpInCidr($ip, $range, $expect)
    {
       $this->assertSame($expect, Net::isIpInCidr($ip, $range));
    }

    public function provideIsIpInCidr(): array
    {
        return [
            ["111.222.111.222", "111.222.111.222/32", true],

            ["111.222.111.221", "111.222.111.222/31", false],
            ["111.222.111.222", "111.222.111.222/31", true],
            ["111.222.111.223", "111.222.111.222/31", true],
            ["111.222.111.224", "111.222.111.222/31", false],
       ];
    }

    /**
     * @dataProvider provideIsPrivateIp
     */
    public function testIsPrivateIp($ip, $expect)
    {
       $this->assertSame($expect, Net::isPrivateIp($ip));
    }

    public function provideIsPrivateIp(): array
    {
        $ret = [
            ["8.8.8.8", false],
        ];

        # 10.0.0.0 – 10.255.255.255
        $ret[] = ["9.255.255.255", false];
        for ($i = 0; $i < 100; $i++) {
            $ret[] = [$this->createIp("10.%d.%d.%d", 0, 0, 0), true];
        }
        $ret[] = ["11.0.0.0", false];

        # 172.16.0.0 – 172.31.255.255
        $ret[] = ["172.15.255.255", false];
        for ($i = 0; $i < 100; $i++) {
            $ret[] = [$this->createIp("172.%d.%d.%d", [16, 31], 0, 0), true];
        }
        $ret[] = ["172.32.0.0", false];

        # 192.168.0.0 – 192.168.255.255
        $ret[] = ["192.167.255.255", false];
        for ($i = 0; $i < 100; $i++) {
            $ret[] = [$this->createIp("192.168.%d.%d", [16, 31], 0, 0), true];
        }
        $ret[] = ["192.169.0.0", false];

        return $ret;
    }

    private function createIp(string $template, ...$ranges)
    {
        $randoms = [];
        foreach ($ranges as $bounds) {
            if ($bounds === 0) {
                $bounds = [0, 255];
            }

            $randoms[] = mt_rand($bounds[0], $bounds[1]);
        }

        return sprintf($template, ...$randoms);
    }
}
