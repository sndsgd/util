<?php

namespace sndsgd;

class Config
{
    /**
     * Config values are stored here
     *
     * @var array<string,mixed>
     */
    protected $values = [];

    /**
     * @param array $values <string,mixed>
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Retrieve a value, or a default value if it doesn't exist
     *
     * @param string $key The key of the value to retrieve
     * @param mixed $default The value to return if $key does not exist
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->values[$key] ?? $default;
    }

    /**
     * Retrieve a value that must exist
     *
     * @param string $key The key of the value to retrieve
     * @return mixed
     * @throws \RuntimeException If the key does not exist
     */
    public function getRequired(string $key)
    {
        if (!isset($this->values[$key])) {
            throw new \RuntimeException(
                "the required config value '$key' was not found"
            );
        }
        return $this->values[$key];
    }
}
