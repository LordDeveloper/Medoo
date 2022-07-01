<?php

namespace Medoo\Drivers;

class PostgreSQL extends Driver
{

    public function __construct(array $options)
    {
        $options['type'] = $options['database_type'] = 'pgsql';

        $this->setDriverOptions($options);
    }
}