<?php

namespace Medoo;

use Amp\Promise;
use function Amp\call;

/**
 * Connecting to specified database
 *
 * @param $driver
 * @param array $options
 * @return DatabaseConnection
 */
function connect($driver, array $options): DatabaseConnection
{
    return new DatabaseConnection(new $driver($options));
}