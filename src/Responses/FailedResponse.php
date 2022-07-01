<?php

namespace Medoo\Responses;

class FailedResponse
{
    public function __construct(
        public $message
    )
    {
    }

    public function getMessage()
    {
        return $this->message;
    }
}