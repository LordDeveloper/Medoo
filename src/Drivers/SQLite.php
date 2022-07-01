<?php

namespace Medoo\Drivers;

class SQLite extends Driver
{

    public function __construct(array $options)
    {
        $options['type'] = $options['database_type'] = 'sqlite';

        $this->setOption(new DriverOption($options));
    }
}