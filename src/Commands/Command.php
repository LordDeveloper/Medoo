<?php

namespace Medoo\Commands;

use Amp\Promise;
use Medoo\Environment;
use Medoo\Responses\Response;

interface Command
{
    /**
     * Execute command
     *
     * @param Environment $environment
     * @return Promise<Response>
     */
    public function execute(Environment $environment): Promise;
}