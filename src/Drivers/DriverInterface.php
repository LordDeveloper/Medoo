<?php

namespace Medoo\Drivers;

use Amp\Promise;

interface DriverInterface
{
    public function create(): Promise;

    public function getDriverOptions(): array;

    public function setDriverOptions(array $driverOption);
}