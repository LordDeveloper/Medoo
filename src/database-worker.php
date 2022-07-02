<?php

use Amp\Parallel\Sync\Channel;
use Medoo\Commands\Command;
use Medoo\Database;
use Medoo\Drivers\DriverOption;
use Medoo\Environment;
use Medoo\Responses\FailureProcessResponse;
use function Opis\Closure\{serialize, unserialize};

return static function (Channel $channel): Generator {
    try {
        $options = yield $channel->receive();

        $database = new Database(new DriverOption($options));
        $environment = new Environment($database);

        yield $channel->send(serialize(true));

        while (true) {
            $command = unserialize(yield $channel->receive());

            assert($command instanceof Command);

            yield $channel->send(serialize(yield $command->execute($environment)));
        }
    } catch (Throwable $e) {
        yield $channel->send(serialize(new FailureProcessResponse($e)));
    }
};