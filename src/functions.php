<?php

namespace Medoo;

use Amp\Promise;
use function Amp\call;

/**
 * Connecting to specified database
 *
 * @param $driver
 * @param array $options
 * @return Promise<Database>
 */
function connect($driver, array $options): Promise
{
    return call(function () use ($driver, $options) {
        $driver = yield (new $driver($options))->create();

        return new DatabaseConnection($driver);
    });
}