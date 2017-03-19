<?php

namespace sndsgd;

class Container
{
    /**
     * Storage for the results of `get()` calls
     *
     * @var <string,mixed>
     */
    protected $cache = [];

    /**
     * Retrieve a value that should not be created more than once
     *
     * @param string $name The key we'll use to store the value once created
     * @param callable $callback A function to created the value if needed
     * @return mixed
     */
    protected function get(string $name, callable $callback)
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        return $this->cache[$name] = call_user_func($callback, $name);
    }

    /**
     * Gracefully remove a value from the cache map
     *
     * @param string $name The key of the value to remove
     * @param callable $callback An optional callback to handle the existing value
     */
    protected function reset(string $name, callable $callback = null)
    {
        if (!isset($this->cache[$name])) {
            return;
        }

        if ($callback !== null) {
            call_user_func($callback, $name, $this->cache[$name]);
        }

        unset($this->cache[$name]);
    }
}
