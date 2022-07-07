<?php

namespace Medoo\Commands;

use Amp\Promise;
use Error;
use Generator;
use Medoo\Environment;
use Medoo\Responses\FailureProcessResponse;
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
                $response = $environment->getDatabase()->{$this->action}(... $this->arguments);
                if ($response instanceof Generator) {
                    $response = yield from $response;
                }
                elseif ($response instanceof Promise) {
                    $response = yield $response;
                }
                return new SuccessProcessResponse(
                    $response
                );
            } catch (Throwable|Error $e) {
                return new FailureProcessResponse($e);
            }
        });
    }
}