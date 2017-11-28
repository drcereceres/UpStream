<?php
namespace UpStream;

/**
 * Basic abstract class to represent a simple struct structure.
 *
 * @since       @todo
 * @abstract
 */
abstract class Struct
{
    /**
     * Prevent non existent properties from being retrieved.
     *
     * @since   @todo
     *
     * @param   string  $property   Property being retrieved.
     *
     * @throws  \RuntimeException
     */
    public function __get($property)
    {
        throw new \RuntimeException(sprintf('Trying to get non-existing property "%s".', $property));
    }

    /**
     * Prevent non existent properties from being set.
     *
     * @since   @todo
     *
     * @param   string  $property   Property being set.
     * @param   mixed   $value      Value being set.
     *
     * @throws  \RuntimeException
     */
    public function __set($property, $value)
    {
        throw new \RuntimeException(sprintf('Trying to set non-existing property "%s".', $property));
    }

    /**
     * Prevent structs from being passed by reference.
     *
     * @since   @todo
     */
    public function __clone()
    {
        foreach ($this as $property => $value) {
            if (is_object($value)) {
                $this->{$property} = clone $value;
            }
        }
    }
}
