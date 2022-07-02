<?php

namespace Medoo\Drivers;

use Amp\Parallel\Context;
use Amp\Parallel\Sync\SynchronizationError;
use Amp\Promise;
use Amp\Sql\TransientResource;
use Amp\Sync\LocalMutex;
use Amp\Sync\Mutex;
use Amp\TimeoutException;
use function Amp\call;
use function Amp\Sync\synchronized;
use function Opis\Closure\{serialize, unserialize};

abstract class Driver implements DriverInterface, TransientResource
{
    const CONTEXT_CLOSE_TIMEOUT = 50;
    public Context\Context $context;
    public array $driverOptions;
    public int $lastUsedAt;
    public Mutex $mutex;

    public function close(): Promise
    {
        return synchronized($this->mutex, function () {
            if (!$this->isAlive()) {
                return;
            }

            try {
                yield Promise\timeout($this->context->join(), self::CONTEXT_CLOSE_TIMEOUT);
            } catch (TimeoutException) {
                $this->context->kill();
            }
        });
    }

    public function create(): Promise
    {
        return call(function () {
            $this->mutex = new LocalMutex();

            $this->context = yield Context\run(__DIR__ . '/../database-worker.php');

            yield $this->context->send($this->getDriverOptions());

            $response = unserialize(yield $this->context->receive());

            if ($response instanceof \Exception || $response instanceof \Error) {
                throw $response;
            }

            $this->lastUsedAt = time();

            return $this;
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
        return $this->context->isRunning();
    }
}