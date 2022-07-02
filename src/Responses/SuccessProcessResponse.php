<?php

namespace Medoo\Responses;

use Medoo\Grammars\PDOStatement;

class SuccessProcessResponse implements Response
{
    public mixed $result;

    public function __construct(mixed $result) {
        if ($result instanceof \PDOStatement) {
            $result = new PDOStatement($result);
        }

        $this->result = $result;
    }

    /**
     * @return mixed
     */
    public function getResult(): mixed
    {
        return $this->result;
    }
}