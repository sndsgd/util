<?php

namespace sndsgd;

class Net
{
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
}
