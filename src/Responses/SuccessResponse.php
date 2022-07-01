<?php

namespace Medoo\Responses;

class SuccessResponse extends Response
{
    public function __construct(mixed $result)
    {
        $this->result = $result;
    }
}