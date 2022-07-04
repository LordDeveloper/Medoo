<?php

namespace Medoo\Responses;

use Exception;
use ReflectionProperty;
use Throwable;

class FailureProcessResponse implements Response
{
    private array $props = [];

    public function __construct(Throwable $e)
    {
        $this->props = [
            'class' => get_class($e),
            'code' => $e->getCode(),
            'line' => $e->getLine(),
            'message' => $e->getMessage(),
            'trace' => $e->getTrace(),
        ];
    }

    public function throw()
    {
        $throwable = new $this->class($this->message);

        foreach ($this->props as $prop => $value) {
            $reflection = new ReflectionProperty(Exception::class, $prop);
            $reflection->setAccessible(true);
            $reflection->setValue($throwable, $value);
        }

        throw $throwable;
    }

    public function __serialize()
    {
        return [
            'props' => $this->props
        ];
    }

    public function __unserialize(array $data)
    {
        $this->props = $data['props'];
    }
}