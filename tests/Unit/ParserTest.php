<?php

namespace PhpTui\CliParser\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\Attribute\Arg;
use PhpTui\CliParser\Attribute\Opt;
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

    public function testPositionalArguments(): void
    {
        $target = new class {
            #[Arg()]
            public ?string $foobar = null;
            #[Arg()]
            public ?string $barfoo = null;
        };
        $this->parse($target, ['cmd', 'foobar', 'barfoo']);

        self::assertEquals('foobar', $target->foobar);
        self::assertEquals('barfoo', $target->barfoo);
    }

    public function testPositionalArgumentWithOptions(): void
    {
        $target = new class {
            #[Arg()]
            public ?string $foobar = null;
            #[Opt()]
            public ?string $option = null;
            #[Arg()]
            public ?string $barfoo = null;
        };
        $this->parse($target, ['cmd', 'foobar', '--option=foo', 'barfoo']);

        self::assertEquals('foobar', $target->foobar);
        self::assertEquals('barfoo', $target->barfoo);
        self::assertEquals('foo', $target->option);
    }

    /**
     * @param list<string> $args
     */
    private function parse(object $target, array $args): void
    {
        (new Parser())->parse($target, $args);
    }
}
