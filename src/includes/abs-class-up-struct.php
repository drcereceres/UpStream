<?php
namespace UpStream;

// @todo

abstract class Struct
{
    public function __get($property)
    {
        throw new \RuntimeException('Trying to get non-existing property ' . $property);
    }

    public function __set($property, $value)
    {
        throw new \RuntimeException('Trying to set non-existing property ' . $property);
    }

    public function __clone()
    {
        foreach ($this as $property => $value) {
            if ( is_object($value)) {
                $this->$property = clone $value;
            }
        }
    }
}
