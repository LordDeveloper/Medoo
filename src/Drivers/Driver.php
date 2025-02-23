<?php

namespace Medoo\Drivers;

use Amp\Parallel\Context;
use Amp\Parallel\Sync\SynchronizationError;
use Amp\Promise;
use Amp\Sql\TransientResource;
use Amp\Sync\LocalMutex;
use Amp\Sync\Mutex;
use Amp\TimeoutException;
use Medoo\Responses\FailureProcessResponse;
use function Amp\call;
use function Amp\Sync\synchronized;
use function Opis\Closure\{serialize, unserialize};

abstract class Driver implements DriverInterface, TransientResource
{
    const CONTEXT_CLOSE_TIMEOUT = 10;
    public Context\Context $context;
    public array $driverOptions;
    public int $lastUsedAt = 0;
    public Mutex $mutex;
    protected static array $connections = [];

    public function close(): Promise
    {
        return synchronized($this->mutex, function () {
            if ($this->isAlive()) {
                try {
                    return yield Promise\timeout($this->context->join(), self::CONTEXT_CLOSE_TIMEOUT);
                } catch (TimeoutException) {
                    $this->context->kill();
                }

                return true;
            }

            return false;
        });
    }

    public function create(): Promise
    {
        return call(function () {
            $options = $this->getDriverOptions();

            return static::$connections[$options['type']] ??= yield call(function () use ($options) {
                $this->mutex = new LocalMutex();

                $this->context = yield Context\run(__DIR__ . '/../database-worker.php');

                yield $this->context->send($options);

                $response = unserialize(yield $this->context->receive());

                if ($response instanceof FailureProcessResponse) {
                    $response->throw();
                }

                $this->lastUsedAt = time();

                return $this;
            });
        });
    }

    public function getDriverOptions(): array
    {
        return $this->driverOptions;
    }

    public function setDriverOptions(array $driverOption)
    {
        $this->driverOptions = $driverOption;
    }

    public function getLastUsedAt(): int
    {
        return $this->lastUsedAt;
    }

    public function send($data): Promise
    {
        return synchronized($this->mutex, function () use ($data) {
            if (!$this->isAlive()) {
                throw new SynchronizationError('Process unexpectedly exited');
            }

            yield $this->context->send(serialize($data));

            $response = unserialize(yield $this->context->receive());

            $this->lastUsedAt = time();

            return $response;
        });
    }

    public function isAlive(): bool
    {
        return isset($this->context) && $this->context->isRunning();
    }
}