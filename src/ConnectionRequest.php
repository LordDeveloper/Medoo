<?php

namespace Medoo;

use Medoo\Drivers\DriverInterface;

class ConnectionRequest
{

    public function __construct(
        public DriverInterface $driver
    )
    {
    }

    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }
}