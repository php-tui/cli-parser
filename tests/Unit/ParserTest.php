<?php

namespace PhpTui\CliParser\Tests\Unit;

use Closure;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\Attribute\Arg;
use PhpTui\CliParser\Attribute\Cmd;
use PhpTui\CliParser\Attribute\Opt;
use PhpTui\CliParser\Error\ParseError;
use PhpTui\CliParser\Parser;

final class ParserTest extends TestCase
{
    #[DataProvider('provideArguments')]
    #[DataProvider('provideOptions')]
    #[DataProvider('provideNestedCommands')]
    public function testParse(Closure $test): void
    {
        $test();
    }
    /**
     * @return Generator<string,array{Closure():void}>
     */
    public static function provideArguments(): Generator
    {
        yield '<foobar>' => [
            function (): void {
                $target = new class {
                    #[Arg()]
                    public ?string $foobar = null;
                };
                self::parse($target, ['cmd', 'foobar']);
                self::assertEquals('foobar', $target->foobar);
            },
        ];
        yield '<foobar> <barfoo>' => [
            function (): void {
                $target = new class {
                    #[Arg()]
                    public ?string $foobar = null;

                    #[Arg()]
                    public ?string $barfoo = null;
                };
                self::parse($target, ['cmd', 'foobar', 'barfoo']);
                self::assertEquals('foobar', $target->foobar);
                self::assertEquals('barfoo', $target->barfoo);
            }
        ];
        yield '<foobar> ... repeat argument' => [
            function (): void {
                $target = new class {
                    #[Arg()]
                    /** @phpstan-ignore-next-line */
                    public array $foobars = [];
                };
                self::parse($target, ['cmd', 'foobar', 'barfoo']);
                self::assertEquals(['foobar', 'barfoo'], $target->foobars);
            }
        ];
        yield '<foobar> <barfoo> --option=<value>' => [
            function (): void {
                $target = new class {
                    #[Arg()]
                    public string $foobar;

                    #[Arg()]
                    public string $barfoo;

                    #[Opt()]
                    public string $option;
                };
                self::parse($target, ['cmd', 'foobar', '--option=foo', 'barfoo']);

                self::assertEquals('foobar', $target->foobar);
                self::assertEquals('barfoo', $target->barfoo);
                self::assertEquals('foo', $target->option);
            }
        ];
        yield '<foobar> where foobar has spaces' => [
            function (): void {
                $target = new class {
                    #[Arg()]
                    public ?string $foobar = null;
                };
                self::parse($target, ['cmd', 'foobar barfoo']);

                self::assertEquals('foobar barfoo', $target->foobar);
            }
        ];
        yield '[<foobar>] optional argument omitted' => [
            function (): void {
                $target = new class {
                    #[Arg(required: false)]
                    public string $foobar = 'bar';
                };
                self::parse($target, ['cmd']);

                self::assertEquals('bar', $target->foobar);
            }
        ];
        yield 'invalid [<foobar>] <barfoo> cannot have optional argument before required argument' => [
            function (): void {
                $target = new class {
                    #[Arg(required: false)]
                    public string $foobar;

                    #[Arg(required: true)]
                    public string $barfoo;
                };

                try {
                    self::parse($target, ['cmd' ,'foo', 'bar']);
                } catch (ParseError $error) {
                    self::assertStringContainsString('Required argument <barfoo> cannot be positioned after optional argument', $error->getMessage());
                    return;
                }
                self::fail('Did not throw exception');
            }
        ];
        yield 'all supported types' => [
            function (): void {
                $target = new class {
                    #[Arg()]
                    public string $string;

                    #[Arg()]
                    public int $int;

                    #[Arg()]
                    public float $float;

                    #[Arg()]
                    public bool $boolean;
                };
                self::parse($target, ['cmd', 'foobar', '12', '12.3', 'true']);

                self::assertEquals('foobar', $target->string);
                self::assertEquals(12, $target->int);
                self::assertEquals(12.3, $target->float);
                self::assertEquals(true, $target->boolean);
            }
        ];
    }

    /**
     * @return Generator<string,array{Closure():void}>
     */
    public static function provideOptions(): Generator
    {
        yield '--on' => [
            function (): void {
                $target = new class {
                    #[Opt()]
                    public bool $on = false;

                    #[Opt()]
                    public bool $off = false;
                };
                self::parse($target, ['cmd', '--on']);

                self::assertSame(true, $target->on);
                self::assertSame(false, $target->off);
            },
        ];
        yield '--on=true with all boolean string values' => [
            function (): void {
                $target = new class {
                    #[Opt()]
                    public bool $on = false;
                };

                foreach ([
                    'false' => false,
                    'off' => false,
                    'no' => false,
                    'true' => true,
                    'on' => true,
                    'yes' => true
                ] as $variant => $expected) {
                    self::parse($target, ['cmd', '--on='.$variant]);
                    self::assertSame($expected, $target->on);
                }

            },
        ];
        yield '--integer=1 --on1' => [
            function (): void {
                $target = new class {
                    #[Opt()]
                    public int $integer = 2;

                    #[Opt()]
                    public string $off = 'off';

                    #[Opt()]
                    public bool $on1 = false;
                };
                self::parse($target, ['cmd', '--integer=1', '--on1']);

                self::assertSame(1, $target->integer);
                self::assertSame('off', $target->off);
                self::assertSame(true, $target->on1);
            },
        ];
        yield '--greeting="hello world"' => [
            function (): void {
                $target = new class {
                    #[Opt()]
                    public ?string $greeting = null;
                };
                self::parse($target, ['cmd', '--greeting=hello world']);

                self::assertSame('hello world', $target->greeting);
            },
        ];
        yield '-g"hello world" short option' => [
            function (): void {
                $target = new class {
                    #[Opt(short:'g')]
                    public ?string $greeting = null;
                };
                self::parse($target, ['cmd', '-ghello world']);

                self::assertSame('hello world', $target->greeting);
            },
        ];
    }

    /**
     * @return Generator<string,array{Closure():void}>
     */
    public static function provideNestedCommands(): Generator
    {
        yield 'ls <path> ...' => [
            function (): void {
                $cli = new class(
                    new #[Cmd()] class {
                        /** @var list<string> */
                        #[Arg()]
                        public array $paths = [];
                    },
                ) {
                    public function __construct(
                        public object $ls,
                    ) {}
                };

                self::parse($cli, ['cmd', 'ls', 'foo.php', 'bar.php']);
                /** @phpstan-ignore-next-line */
                self::assertEquals(['foo.php', 'bar.php'], $cli->ls->paths);
            },
        ];
        yield '--flag ls <path> ...' => [
            function (): void {
                $cli = new class(
                    false,
                    new #[Cmd()] class {
                        /** @var list<string> */
                        #[Arg()]
                        public array $paths = [];
                    },
                ) {
                    public function __construct(
                        #[Opt()]
                        public bool $flag,
                        public object $ls,
                    ) {}
                };

                self::parse($cli, ['cmd', '--flag', 'ls', 'foo.php', 'bar.php']);
                self::assertEquals(true, $cli->flag);
                /** @phpstan-ignore-next-line */
                self::assertEquals(['foo.php', 'bar.php'], $cli->ls->paths);
            },
        ];
        yield 'ls --flag <path> ...' => [
            function (): void {
                $cli = new class(
                    new #[Cmd()] class {
                        #[Opt()]
                        public bool $flag;
                        /** @var list<string> */
                        #[Arg()]
                        public array $paths = [];
                    },
                ) {
                    public function __construct(
                        public object $ls,
                    ) {}
                };

                self::parse($cli, ['cmd', 'ls', '--flag', 'foo.php', 'bar.php']);
                /** @phpstan-ignore-next-line */
                self::assertEquals(true, $cli->ls->flag);
                /** @phpstan-ignore-next-line */
                self::assertEquals(['foo.php', 'bar.php'], $cli->ls->paths);
            },
        ];
    }

    public function testExample(): void
    {
        $cli = new #[Cmd('My App', help: 'Application to list and remove files')] class(
            new #[Cmd('rm', help: 'Remove files')] class {
                #[Opt(help: 'Force removal')]
                public bool $force = false;

                #[Opt(help: 'Recursively remove files', short: 'r')]
                public bool $recursive = false;

                /** @var list<string> */
                #[Opt(help: 'Paths to remove', name: 'path', type: 'path')]
                public array $paths = [];
            },
            new #[Cmd('ls', help: 'List files')] class {
                /** @var list<string> */
                #[Opt(help: 'Paths to list', name: 'path', type: 'path')]
                public array $paths = [];
            },
        ) {
            public function __construct(
                public object $rmCmd,
                public object $lsCmd,
            ) {}
        };

        self::parse($cli, ['cmd', 'rm', '--force', '-r', 'path1.php', 'path2.php']);
    }

    /**
     * @param list<string> $args
     */
    private static function parse(object $target, array $args): void
    {
        (new Parser())->parse($target, $args);
    }
}
