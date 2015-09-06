<?php

namespace sndsgd;

use \Exception;


/**
 * Storage for various values
 */
class Storage extends Singleton
{
    /**
     * Storage for values
     *
     * @var array<string,mixed>
     */
    private $values = [];

    /**
     * Get a value by calling its key as a static method
     *
     * @param string $name The name of the called method
     * @param array $args The arguments provided to the method
     * @return mixed
     */
    public static function __callStatic($name, $args)
    {
        $instance = static::getInstance();
        return $instance->get($name);
    }

    /**
     * Import values
     *
     * @param array<string,mixed> $values
     */
    public function import(array $values)
    {
        $this->values = $values;
    }

    /**
     * Export the current values
     *
     * @return array<string,mixed>
     */
    public function export()
    {
        return $this->values;
    }

    /**
     * Add an object to the container
     *
     * @param string $name A key to used for retrieving the value later
     * @param mixed $value The value to add
     */
    public function set($name, $value)
    {
        $this->values[$name] = $value;
    }

    /**
     * Retrieve a value
     *
     * @param string $name
     * @return mixed
     * @throws \Exception If the provided name doesn't exist
     */
    public function get($name)
    {
        if (!array_key_exists($name, $this->values)) {
            throw new Exception("no value exists for '$name'");
        }
        return $this->values[$name];
    }

    /**
     * Determine if a given name exists
     *
     * @param string $name
     * @return boolean
     */
    public function has($name)
    {
        return array_key_exists($name, $this->values);
    }
}
