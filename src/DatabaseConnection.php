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
use function Amp\coroutine;

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

    public function action(callable $actions): Promise
    {
        return call(function () use ($actions) {
            $actions = coroutine($actions);

            if (is_callable($actions)) {
                yield $this->beginTransaction();

                try {
                    $result = yield $actions($this);

                    if ($result === false) {
                        yield $this->rollBack();
                    } else {
                        yield $this->commit();
                    }
                } catch (Exception $e) {
                    yield $this->rollBack();
                    throw $e;
                }
            }
        });
    }

    public function getLastUsedAt(): int
    {
        return $this->driver->getLastUsedAt();
    }
}