<?php

namespace sndsgd;

class Container
{
    /**
     * Storage for singleton instances
     *
     * @var <string,mixed>
     */
    protected $singletons = [];

    /**
     * Retrieve a value that should not be created more than once
     *
     * @param string $name The key we'll use to store the value once created
     * @param callable $callback A function to created the value if needed
     * @return mixed
     */
    protected function getSingleton(string $name, callable $callback)
    {
        if (isset($this->singletons[$name])) {
            return $this->singletons[$name];
        }

        return $this->singletons[$name] = call_user_func($callback, $name);
    }

    /**
     * Gracefully remove a value from the singleton map
     *
     * @param string $name The key of the value to remove
     * @param callable $callback An optional callback to handle the existing value
     */
    protected function resetSingleton(string $name, callable $callback = null)
    {
        if (!isset($this->singletons[$name])) {
            return;
        }

        if ($callback !== null) {
            call_user_func($callback, $name, $this->singletons[$name]);
        }

        unset($this->singletons[$name]);
    }
}
