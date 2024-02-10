<?php

namespace PhpTui\CliParser\Application;

use PhpTui\CliParser\Application\Exception\MiddlewareExhausted;

final class Handler
{
    /**
     * @var list<Middleware>
     */
    private $middlewares;

    public function __construct(Middleware ...$middlewares)
    {
        $this->middlewares = array_values($middlewares);
    }
    /**
     * @param Context<object,object> $request
     */
    public function handle(Context $context): int
    {
        $middleware = array_shift($this->middlewares);

        if (!$middleware) {
            throw new MiddlewareExhausted(
                'Middleware exhausted (no middleware handled the request)'
            );
        }

        return $middleware->handle($this, $context);
    }
}
