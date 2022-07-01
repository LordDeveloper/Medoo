<?php

namespace Medoo\Drivers;

class MySQL implements Driver
{

    public function getDriver(): string
    {
        return 'mysql';
    }
}