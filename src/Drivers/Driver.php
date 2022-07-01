<?php

namespace Medoo\Drivers;

use ArrayAccess;

abstract class Driver implements DriverInterface, ArrayAccess
{
    public DriverOption $driverOption;

    public function getOption(): DriverOption
    {
        return $this->driverOption;
    }

    public function setOption(DriverOption $option)
    {
        $this->driverOption = $option;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->driverOption[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->driverOption[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->driverOption[] = $value;
        } else {
            $this->driverOption[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->driverOption[$offset]);
    }
}