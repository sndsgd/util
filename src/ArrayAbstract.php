<?php

namespace sndsgd;

abstract class ArrayAbstract implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * The array values are stored here
     *
     * @var array
     */
    protected $values;

    /**
     * Whether the array may be altered after it is created
     *
     * @var bool
     */
    protected $isReadOnly;

    /**
     * @param array $values
     * @param bool $isReadOnly
     */
    public function __construct(array $values, bool $isReadOnly = false)
    {
        $this->values = $values;
        $this->isReadOnly = $isReadOnly;
    }

    /**
     * @see http://php.net/manual/countable.count
     * @return int
     */
    public function count()
    {
        return count($this->values);
    }

    /**
     * @see http://php.net/manual/arrayaccess.offsetexists
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->values[$offset]);
    }

    /**
     * @see http://php.net/manual/arrayaccess.offsetget
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->values[$offset] : null;
    }

    /**
     * @see http://php.net/manual/arrayaccess.offsetset
     * @param mixed $offset
     * @param mixed $value
     * @throws \RuntimeException
     */
    public function offsetSet($offset, $value)
    {
        if ($this->isReadOnly) {
            $class = get_class($this);
            throw new \RuntimeException(
                "failed to set array value; $class is read only"
            );
        } elseif ($offset === null) {
            $this->values[] = $value;
        } else {
            $this->values[$offset] = $value;
        }
    }

    /**
     * @see http://php.net/manual/arrayaccess.offsetunset
     * @param mixed $offset
     * @throws \RuntimeException
     */
    public function offsetUnset($offset)
    {
        if ($this->isReadOnly) {
            $class = get_class($this);
            throw new \RuntimeException(
                "failed to unset array value; $class is read only"
            );
        }
        unset($this->values[$offset]);
    }

    /**
     * @see http://php.net/manual/iteratoraggregate.getiterator
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->values);
    }
}
