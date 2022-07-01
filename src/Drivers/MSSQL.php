<?php

namespace Medoo\Drivers;

class MSSQL extends Driver
{

    public function __construct(array $options)
    {
        $options['type'] = $options['database_type'] = 'mssql';

        $this->setDriverOptions($options);
    }
}