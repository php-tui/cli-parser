<?php

namespace PhpTui\CliParser\Tests\Unit\Application;

use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\Application\CommandHandler;
use PhpTui\CliParser\Application\Context;
use PhpTui\CliParser\Application\Exception\CommandHandlerNotFound;
use PhpTui\CliParser\Metadata\ApplicationDefinition;

final class CommandHandlerTest extends TestCase
{
    public function testHandleCallable(): void
    {
        $cmd = new class() {};

        $handler = new CommandHandler([
            $cmd::class => function (object $cmd) use (&$called):int {
                $called = true;
                return 127;
            }
        ]);
        $app = new ApplicationDefinition('foo');

        self::assertEquals(127, $handler->handle(
            new Context($app, $cmd, $cmd)
        ));
    }

    public function testHandlerNotFound(): void
    {
        $this->expectException(CommandHandlerNotFound::class);

        $cmd = new class() {};
        $app = new ApplicationDefinition('foo');

        $handler = new CommandHandler([
        ]);

        self::assertEquals(127, $handler->handle(new Context($app, $cmd, $cmd)));
    }
}
