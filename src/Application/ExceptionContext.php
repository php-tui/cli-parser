<?php

namespace PhpTui\CliParser\Application;

use PhpTui\CliParser\Metadata\ApplicationDefinition;
use Throwable;

final readonly class ExceptionContext
{
    public function __construct(
        public ApplicationDefinition $applicationDefinition,
        public Throwable $throwable
    ) {
    }
}
