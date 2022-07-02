<?php

namespace Medoo\Commands;

use Amp\Promise;
use Error;
use Medoo\Environment;
use Medoo\Responses\FailureProcessResponse;
use Medoo\Responses\Response;
use Medoo\Responses\SuccessProcessResponse;
use Throwable;
use function Amp\call;

class CallActionCommand implements Command
{
    public string $action;
    public array $arguments;

    public function __construct($data)
    {
        [$this->action, $this->arguments] = $data;
    }

    public function execute(Environment $environment): Promise
    {
        return call(function () use ($environment) {
            try {
                return new SuccessProcessResponse(
                    yield $environment->getDatabase()->{$this->action}(... $this->arguments)
                );
            } catch (Throwable|Error $e) {
                return new FailureProcessResponse($e);
            }
        });
    }
}