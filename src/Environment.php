<?php

namespace Medoo;

class Environment
{
    public function __construct(
        public Database $database
    ) {}

    /**
     * @return Database
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }
}