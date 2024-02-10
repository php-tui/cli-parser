<?php

namespace PhpTui\CliParser\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\ApplicationBuilder;
use PhpTui\CliParser\Application\Context;
use PhpTui\CliParser\Application\Handler;
use PhpTui\CliParser\Application\Middleware;

final class ApplicationBuilderTest extends TestCase
{
    public function testBuildAndRun(): void
    {
        $helloCmd = new class() {};
        $spec = new class() {
            public object $hello;
        };
        $spec->hello = $helloCmd;

        $exitCode = ApplicationBuilder::fromSpecification($spec)
            ->addHandler($helloCmd::class, function (Context $context):int {
                return 1;
            })
            ->prependMiddleware(new class implements Middleware {
                /** @param Context<object,object> $context */
                public function handle(Handler $handler, Context $context): int
                {
                    return $handler->handle($context) + 1;
                }
            })
            ->prependMiddleware(new class implements Middleware {
                /** @param Context<object,object> $context */
                public function handle(Handler $handler, Context $context): int
                {
                    return $handler->handle($context) + 2;
                }
            })
            ->build()->run(['', 'hello']);

        self::assertEquals(4, $exitCode);
    }
}
