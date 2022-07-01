<?php

namespace Medoo\Drivers;

interface DriverInterface
{

    public function getOption(): DriverOption;

    public function setOption(DriverOption $option);
}