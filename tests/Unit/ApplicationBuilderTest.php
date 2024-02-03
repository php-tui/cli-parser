<?php

namespace PhpTui\CliParser\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\ApplicationBuilder;
use PhpTui\CliParser\Attribute\App;
use PhpTui\CliParser\Attribute\Cmd;

final class ApplicationBuilderTest extends TestCase
{
    public function testBuildAndRun(): void
    {
        $helloCmd = new #[Cmd()] class() {};
        $spec = new #[App] class() {
            public object $hello;
        };
        $spec->hello = $helloCmd;

        $exitCode = ApplicationBuilder::fromSpecification($spec)
            ->addHandler($helloCmd::class, function (object $cmd):int {
                return 1;
            })
            ->build()->run(['', 'hello']);

        self::assertEquals(1, $exitCode);
    }
}
