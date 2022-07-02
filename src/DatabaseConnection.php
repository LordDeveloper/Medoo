<?php

namespace Medoo;

use Amp\Parallel\Context\StatusError;
use Amp\Parallel\Sync\SynchronizationError;
use Amp\Promise;
use Amp\Sql\ConnectionException;
use Error;
use Exception;
use Medoo\Drivers\Driver;
use function Amp\call;
use function Opis\Closure\unserialize;

class DatabaseConnection
{
    public Driver $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function __call($name, $arguments)
    {
        return call(function () use ($arguments, $name) {
            $response = yield $this->driver->send([$name, $arguments]);

            if ($response instanceof Exception || $response instanceof Error) {
                throw $response;
            }

            return $response;
        });
    }

    public function __destruct()
    {
        Promise\rethrow($this->close());
    }

    public function close(): Promise
    {
        return call(function () {
            try {
                if (!$this->isAlive()) {
                    return;
                }

                yield $this->driver->close();
            } catch (StatusError $e) {
                throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
            } catch (SynchronizationError) {
            }
        });
    }

    public function isAlive(): bool
    {
        return $this->driver->isAlive();
    }

    public function getLastUsedAt(): int
    {
        return $this->driver->getLastUsedAt();
    }
}