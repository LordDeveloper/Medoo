<?php

namespace Medoo\Drivers;

class Sybase extends Driver
{

    public function __construct(array $options)
    {
        $options['type'] = $options['database_type'] = 'sybase';

        $this->setDriverOptions($options);
    }
}