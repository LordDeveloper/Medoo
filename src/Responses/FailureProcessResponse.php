<?php

namespace Medoo\Responses;

use Throwable;

class FailureProcessResponse implements Response
{
    private Throwable $exception;

    public function __construct(Throwable $e)
    {
        $this->exception = $e;
    }

    public function throw()
    {
        throw $this->exception;
    }

    public function __serialize()
    {
        return [
            'exception' => $this->exception,
        ];
    }

    public function __unserialize(array $data)
    {
        $this->exception = $data['exception'];
    }
}