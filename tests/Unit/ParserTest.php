<?php

namespace PhpTui\CliParser\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\Attribute\Arg;
use PhpTui\CliParser\Parser;

final class ParserTest extends TestCase
{
    public function testPositionalArgument(): void
    {
        $target = new class {
            #[Arg()]
            public ?string $foobar = null;
        };

        $this->parse($target, ['cmd', 'foobar']);

        self::assertEquals('foobar', $target->foobar);
    }

    /**
     * @param list<string> $cmd
     */
    private function parse(object $target, array $cmd): void
    {
        (new Parser())->parse($target, [
            'cmd',
            'foobar',
        ]);
    }
}
