<?php

namespace Medoo\Drivers;

class MySQL extends Driver
{
    public function __construct(array $options)
    {
        $options['type'] = $options['database_type'] = 'mysql';

        $this->setDriverOptions($options);
    }
}