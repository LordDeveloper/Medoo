<?php

namespace Medoo\Drivers;

class MsSQL extends Driver
{

    public function __construct(array $options)
    {
        $options['type'] = $options['database_type'] = 'mssql';

        $this->setOption(new DriverOption($options));
    }
}