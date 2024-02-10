<?php

namespace PhpTui\CliParser\Tests\Unit\Application;

use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\Application\Context;
use PhpTui\CliParser\Application\Exception\MiddlewareExhausted;
use PhpTui\CliParser\Application\Handler;
use PhpTui\CliParser\Application\Middleware;
use PhpTui\CliParser\Application\Middleware\ClosureMiddleware;
use PhpTui\CliParser\Metadata\ApplicationDefinition;
use PhpTui\CliParser\Metadata\CommandDefinition;
use stdClass;

final class HandlerTest extends TestCase
{
    public function testExceptionWhenNoMiddlewares(): void
    {
        $this->expectException(MiddlewareExhausted::class);
        $this->createHandler()->handle($this->createContext());
    }

    public function testMiddlewareReturnsResponse(): void
    {
        $exitCode = $this->createHandler(
            new ClosureMiddleware(function (Handler $handler, Context $context) {
                return 1;
            })
        )->handle($this->createContext());

        self::assertEquals(1, $exitCode);
    }

    public function testMiddlewareDelegatesToNextMiddleware(): void
    {
        $exitCode = $this->createHandler(
            new ClosureMiddleware(function (Handler $handler, Context $context) {
                return $handler->handle($context) + 1;
            }),
            new ClosureMiddleware(function (Handler $handler, Context $context) {
                return $handler->handle($context) + 1;
            }),
            new ClosureMiddleware(function (Handler $handler, Context $context) {
                return 1;
            })
        )->handle($this->createContext());

        self::assertEquals(3, $exitCode);
    }

    private function createHandler(Middleware ...$middlewares): Handler
    {
        return (new Handler(...$middlewares));
    }
    /**
     * @return Context<object,object>
     */
    private function createContext(): Context
    {
        return new Context(
            new ApplicationDefinition('foo'),
            new CommandDefinition('foo', 'foo'),
            new stdClass(),
            new stdClass()
        );
    }
}
