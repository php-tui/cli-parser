<?php

namespace PhpTui\CliParser\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\ApplicationBuilder;
use PhpTui\CliParser\Application\Context;

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
            ->build()->run(['', 'hello']);

        self::assertEquals(1, $exitCode);
    }
}
