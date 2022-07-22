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
use PDOStatement;
use function Amp\call;

/**
 * @method PDOStatement query(string $statement, array $map = [])
 * @method PDOStatement exec(string $statement, array $map = [], callable $callback = null)
 * @method Raw raw(string $string, array $map = [])
 * @method PDOStatement create(string $table, $columns, $options = null)
 * @method PDOStatement drop(string $table)
 * @method array select(string $table, $join, $columns = null, $where = null)
 * @method PDOStatement insert(string $table, array $values, string $primaryKey = null)
 * @method PDOStatement update(string $table, $data, $where = null)
 * @method PDOStatement delete(string $table, $where)
 * @method PDOStatement replace(string $table, array $columns, $where = null)
 * @method get(string $table, $join = null, $columns = null, $where = null)
 * @method bool has(string $table, $join, $where = null)
 * @method array rand(string $table, $join = null, $columns = null, $where = null)
 * @method int count(string $table, $join = null, $column = null, $where = null)
 * @method string avg(string $table, $join, $column = null, $where = null)
 * @method string max(string $table, $join, $column = null, $where = null)
 * @method string min(string $table, $join, $column = null, $where = null)
 * @method string sum(string $table, $join, $column = null, $where = null)
 * @method void action(callable $actions)
 * @method string id(string $name = null)
 */
class DatabaseConnection
{
    public Driver $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    private function send($data): Promise
    {
        return call(function () use ($data) {
            yield $this->driver->create();

            return yield $this->driver->send($data);
        });
    }

    public function __get($name)
    {
        return call(function () use ($name) {
            $response = yield $this->send(
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
            $response = yield $this->send(
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
                if ($this->isAlive()) {
                    return yield $this->driver->close();
                }
            } catch (StatusError $e) {
                throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
            } catch (SynchronizationError) {
            }

            return false;
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