<?php

namespace Medoo;

use Amp\Parallel\Context\StatusError;
use Amp\Parallel\Sync\SynchronizationError;
use Amp\Promise;
use Amp\Sql\ConnectionException;
use Medoo\Commands\CallActionCommand;
use Medoo\Commands\CallPropertyCommand;
use Medoo\Drivers\Driver;
use Medoo\Responses\FailureProcessResponse;
use Medoo\Responses\SuccessProcessResponse;
use function Amp\call;

class DatabaseConnection
{
    public Driver $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function __get($name)
    {
        return call(function () use ($name) {
            $response = yield $this->driver->send(
                new CallPropertyCommand($name)
            );

            if ($response instanceof FailureProcessResponse) {
                $response->throw();
            } else {
                if ($response instanceof SuccessProcessResponse) {
                    return $response->getResult();
                }
            }
        });
    }

    public function __call($name, $arguments)
    {
        return call(function () use ($arguments, $name) {
            $response = yield $this->driver->send(
                new CallActionCommand([$name, $arguments])
            );

            if ($response instanceof FailureProcessResponse) {
                $response->throw();
            } else {
                if ($response instanceof SuccessProcessResponse) {
                    return $response->getResult();
                }
            }
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