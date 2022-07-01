<?php

namespace Medoo\Drivers;

class Oracle extends Driver
{

    public function __construct(array $options)
    {
        $options['type'] = $options['database_type'] = 'oracle';

        $this->setOption(new DriverOption($options));
    }
}