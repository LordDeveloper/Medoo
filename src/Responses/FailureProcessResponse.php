<?php

namespace Medoo\Responses;

use Throwable;

class FailureProcessResponse
{
    public $class;
    public $message;
    public $traces;

    public function __construct(Throwable $e)
    {
        $this->class = get_class($e);
        $this->message = $e->getMessage();
        $this->traces = $e->getTraceAsString();
    }

    public function throw()
    {
        throw new $this->class($this->message);
    }

    public function __serialize()
    {
        return [
            'class' => $this->class,
            'message' => $this->message,
            'traces' => $this->traces,
        ];
    }

    public function __unserialize(array $data)
    {
        $this->class = $data['class'];
        $this->message = $data['message'];
        $this->traces = $data['traces'];
    }
}