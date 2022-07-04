<?php

namespace Medoo\Responses;

use Exception;
use ReflectionProperty;
use Throwable;

class FailureProcessResponse implements Response
{
    private string $class;

    private array $props = [];

    public function __construct(Throwable $e)
    {
        $this->class = get_class($e);

        $this->props = [
            'code' => $e->getCode(),
            'line' => $e->getLine(),
            'message' => $e->getMessage(),
            'trace' => $e->getTrace(),
        ];
    }

    public function throw()
    {
        $throwable = new $this->class();

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
            'class' => $this->class,
            'props' => $this->props
        ];
    }

    public function __unserialize(array $data)
    {
        $this->class = $data['class'];
        $this->props = $data['props'];
    }
}