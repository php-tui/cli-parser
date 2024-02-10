<?php

namespace PhpTui\CliParser\Application\Middleware;

use Closure;
use PhpTui\CliParser\Application\Context;
use PhpTui\CliParser\Application\Handler;
use PhpTui\CliParser\Application\Middleware;

final class ClosureMiddleware implements Middleware
{
    public function __construct(private Closure $closure)
    {
    }

    public function handle(Handler $handler, Context $context): int
    {
        return ($this->closure)($handler, $context);
    }
}
