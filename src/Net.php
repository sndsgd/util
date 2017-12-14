<?php

namespace sndsgd;

class Net
{
    /**
     * The private IPv4 ranges in CIDR notation
     *
     * @var array<string>
     */
    const PRIVATE_IPV4_RANGES = [
        "10.0.0.0/8",
        "172.16.0.0/12",
        "192.168.0.0/16",
    ];

    /**
     * Ping a host
     *
     * @param string $host An ip address or hostname
     * @param int $port The port to connect to
     * @param int $timeout Seconds to allow a connection attempt
     * @return bool
     */
    public static function ping(string $host, int $port, int $timeout = 5): bool
    {
        if ($fp = @fsockopen($host, $port, $errCode, $errStr, $timeout)) {
            fclose($fp);
            return true;
        }
        return false;
    }

    /**
     * Parse a CIDR address range into the ip and network mask portions
     *
     * @param string $cidr The ip range to parse
     * @return array The ip and network mask `[0 => ip, 1 => netmask]`
     */
    public static function parseCidr(string $cidr): array
    {
        $pos = strpos($cidr, "/");
        if ($pos === false) {
            throw new \UnexpectedValueException(
                "invalid CIDR; missing expected '/' delimeter"
            );
        } elseif ($pos === 0) {
            throw new \UnexpectedValueException(
                "invalid CIDR; missing expected ip"
            );
        }

        $ip = substr($cidr, 0, $pos);
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new \UnexpectedValueException(
                "invalid CIDR ip; '$ip' is not a valid IPv4 address"
            );
        }

        $netmask = (int) substr($cidr, $pos + 1);
        if ($netmask < 0 || $netmask > 32) {
            throw new \UnexpectedValueException(
                "invalid CIDR network mask; expecting a value between 0 and 32"
            );
        }

        return [$ip, $netmask];
    }

    /**
     * Determine whether an ip address is within a given CIDR
     *
     * @param string $ip The ip to test
     * @param string $cidr The range to evaluate
     * @return bool
     */
    public static function isIpInCidr(string $ip, string $cidr): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new \UnexpectedValueException(
                "invalid value for ip; expecting an IPv4 address"
            );
        }

        list($range, $netmask) = static::parseCidr($cidr);
        $netmask = ~((1 << (32 - $netmask)) - 1);
        return ((ip2long($ip) & $netmask) === ip2long($range));
    }

    /**
     * Determine whether a given ip is in one of the private network ranges
     *
     * @param string $ip The ip to test
     * @return bool
     */
    public static function isPrivateIp(string $ip): bool
    {
        foreach (self::PRIVATE_IPV4_RANGES as $range) {
            if (self::isIpInCidr($ip, $range)) {
                return true;
            }
        }

        return false;
    }
}
