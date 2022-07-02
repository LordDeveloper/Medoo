<?php

namespace Medoo\Grammars;

use PDO;

class PDOStatement
{
    public $queryString = '';

    public function __construct(\PDOStatement $statement)
    {
        $this->queryString = $statement->queryString;
    }

    public function __serialize()
    {
        return [
            'queryString' => $this->queryString
        ];
    }
}