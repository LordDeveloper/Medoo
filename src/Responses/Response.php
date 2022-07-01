<?php

namespace Medoo\Responses;

abstract class Response
{
    protected mixed $result;

    public function getResult(): mixed
    {
        return $this->result;
    }
}