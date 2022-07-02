<?php

namespace Medoo\Commands;

use Amp\Promise;
use Error;
use Medoo\Environment;
use Medoo\Responses\FailureProcessResponse;
use Medoo\Responses\SuccessProcessResponse;
use Throwable;
use function Amp\call;

class CallPropertyCommand implements Command
{
    public function __construct(
        public string $property
    ) {}

    public function execute(Environment $environment): Promise
    {
        return call(function () use ($environment) {
            try {
                return new SuccessProcessResponse(
                    $environment->getDatabase()->{$this->property}
                );
            } catch (Throwable|Error $e) {
                return new FailureProcessResponse($e);
            }
        });
    }
}