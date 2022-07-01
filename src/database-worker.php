<?php

use Amp\Parallel\Sync\Channel;
use Medoo\Database;
use Medoo\Drivers\DriverOption;

return static function (Channel $channel): Generator {
    try {
        $options = yield $channel->receive();

        $database = new Database(new DriverOption($options));

        yield $channel->send(true);

        while (true) {
            try {
                [$command, $arguments] = yield $channel->receive();

                $response = yield $database->{$command}(... $arguments);
                try {
                    serialize($response);

                    yield $channel->send($response);
                } catch (Throwable $e) {
                    yield $channel->send(null);
                }
            } catch (Throwable $e) {
                yield $channel->send($e);
            }
        }
    } catch (Throwable $e) {
        yield $channel->send($e);
    }
};