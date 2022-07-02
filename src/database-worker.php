<?php

use Amp\Parallel\Sync\Channel;
use Medoo\Database;
use Medoo\Drivers\DriverOption;
use function Opis\Closure\{serialize, unserialize};

return static function (Channel $channel): Generator {
    try {
        $options = yield $channel->receive();

        $database = new Database(new DriverOption($options));

        yield $channel->send(serialize(true));

        while (true) {
            try {
                [$command, $arguments] = unserialize(yield $channel->receive());

                $response = yield $database->{$command}(... $arguments);

                if ($response instanceof PDOStatement) {
                    $response = new \Medoo\Grammars\PDOStatement($response);
                }

                yield $channel->send(serialize($response));
            } catch (Throwable $e) {
                yield $channel->send(serialize($e));
            }
        }
    } catch (Throwable $e) {
        yield $channel->send(serialize($e));
    }
};