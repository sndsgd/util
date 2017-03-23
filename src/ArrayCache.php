<?php

namespace sndsgd;

/**
 * Ephemeral value cache
 * Uses a nested storage structure so groups of values can be removed easily
 */
class ArrayCache
{
    /**
     * Value storage
     *
     * @var array<string,array<string,mixed>>
     */
    protected $values = [];

    /**
     * Store a value
     *
     * @param string $group The name of the group
     * @param string $key The unique identifier for the value to store
     * @param mixed $value The value to store
     * @return \sndsgd\ArrayCache
     */
    public function set(string $group, string $key, $value): ArrayCache
    {
        if ($group === "") {
            throw new \InvalidArgumentException(
                "invalid group provided; expecting a non empty string"
            );
        }

        if ($key === "") {
            throw new \InvalidArgumentException(
                "invalid key provided; expecting a non empty string"
            );
        }

        if ($value === null) {
            throw new \InvalidArgumentException(
                "invalid value provided; expecting anything but null"
            );
        }

        if (!isset($this->values[$group])) {
            $this->values[$group] = [];
        }

        $this->values[$group][$key] = $value;
        return $this;
    }

    /**
     * Retrieve a value from cache
     *
     * @param string $group The name of the group
     * @param string $key The unique identifier for the value to retrieve
     * @return mixed
     */
    public function get(string $group, string $key)
    {
        return $this->values[$group][$key] ?? null;
    }

    /**
     * Remove a value or an entire group of values from storage
     *
     * @param string $group The group to remove
     * @param string $key The identifier of the value to remove
     */
    public function remove(string $group = "", string $key = ""): ArrayCache
    {
        $isGroupEmpty = ($group === "");
        $isKeyEmpty = ($key === "");

        if ($isGroupEmpty && !$isKeyEmpty) {
            throw new \LogicException(
                "removing a value by key is only possible when a group ".
                "is provided"
            );
        }

        if ($isKeyEmpty) {
            unset($this->values[$group]);
        } else {
            unset($this->values[$group][$key]);
        }

        return $this;
    }
}
