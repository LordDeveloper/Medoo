<?php

namespace Medoo;

use Amp\Promise;
use function Amp\call;

function connect($driver, array $options): Promise
{
    return call(function () use ($driver, $options) {
        $driver = yield (new $driver($options))->create();

        return new DatabaseConnection($driver);
    });
}