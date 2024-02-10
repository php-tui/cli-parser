<?php

namespace PhpTui\CliParser\Tests\Unit\Parser;

use Closure;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\Attribute\Arg;
use PhpTui\CliParser\Attribute\Cmd;
use PhpTui\CliParser\Attribute\Opt;
use PhpTui\CliParser\Error\ParseErrorWithContext;
use PhpTui\CliParser\Metadata\Loader;
use PhpTui\CliParser\Parser\Parser;

final class ParserTest extends TestCase
{
    /**
     * @param Closure(): void $test
     */
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
                self::parse($target, ['foobar']);
                self::assertEquals('foobar', $target->foobar);
            },
        ];
        yield '<foobar> required argument not provided' => [
            function (): void {
                $target = new class {
                    #[Arg()]
                    public string $foobar;
                };
                try {
                    self::parse($target, []);
                    self::fail('Exception not thrown');
                } catch (ParseErrorWithContext $e) {
                    self::assertStringContainsString(
                        'Missing required argument(s) <foobar> in command "__ROOT__"',
                        $e->getMessage()
                    );
                }
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
                self::parse($target, ['foobar', 'barfoo']);
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
                self::parse($target, ['foobar', 'barfoo']);
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
                self::parse($target, ['foobar', '--option=foo', 'barfoo']);

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
                self::parse($target, ['foobar barfoo']);

                self::assertEquals('foobar barfoo', $target->foobar);
            }
        ];
        yield '[<foobar>] optional argument omitted' => [
            function (): void {
                $target = new class {
                    #[Arg(required: false)]
                    public string $foobar = 'bar';
                };
                self::parse($target, []);

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
                    self::parse($target, ['foo', 'bar']);
                } catch (ParseErrorWithContext $error) {
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
                self::parse($target, ['foobar', '12', '12.3', 'true']);

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
                self::parse($target, ['--on']);

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
                    self::parse($target, ['--on='.$variant]);
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
                self::parse($target, ['--integer=1', '--on1']);

                self::assertSame(1, $target->integer);
                self::assertSame('off', $target->off);
                self::assertSame(true, $target->on1);
            },
        ];
        yield '--integer=1,2,3' => [
            function (): void {
                $target = new class {
                    /** @var string[] */
                    #[Opt()]
                    public array $integers = [];
                };
                self::parse($target, ['--integers=1,2,3']);

                self::assertSame(['1', '2', '3'], $target->integers);
            },
        ];
        yield '--integer=1,2,3 cast to int' => [
            function (): void {
                $target = new class {
                    /** @var int[] */
                    #[Opt(type: 'int')]
                    public array $integers = [];
                };
                self::parse($target, ['--integers=1,2,3']);

                self::assertSame([1, 2, 3], $target->integers);
            },
        ];
        yield '--integer=1,2,3 cast to float' => [
            function (): void {
                $target = new class {
                    /** @var int[] */
                    #[Opt(type: 'float')]
                    public array $integers = [];
                };
                self::parse($target, ['--integers=1,2,3']);

                self::assertSame([1.0, 2.0, 3.0], $target->integers);
            },
        ];
        yield '--integer=1,2,3 --on1' => [
            function (): void {
                $target = new class {
                    /** @var string[] */
                    #[Opt()]
                    public array $integers = [];
                };
                self::parse($target, ['--integers=1,2,3']);

                self::assertSame(['1', '2', '3'], $target->integers);
            },
        ];
        yield '--greeting="hello world"' => [
            function (): void {
                $target = new class {
                    #[Opt()]
                    public ?string $greeting = null;
                };
                self::parse($target, ['--greeting=hello world']);

                self::assertSame('hello world', $target->greeting);
            },
        ];
        yield '-g"hello world" short option' => [
            function (): void {
                $target = new class {
                    #[Opt(short:'g')]
                    public ?string $greeting = null;
                };
                self::parse($target, ['-ghello world']);

                self::assertSame('hello world', $target->greeting);
            },
        ];
        yield '-g short flag' => [
            function (): void {
                $target = new class {
                    #[Opt(short:'g')]
                    public bool $greeting;
                };
                self::parse($target, ['-g']);

                self::assertTrue($target->greeting);
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
                    ) {
                    }
                };

                self::parse($cli, ['ls', 'foo.php', 'bar.php']);
                /** @phpstan-ignore-next-line */
                self::assertEquals(['foo.php', 'bar.php'], $cli->ls->paths);
            },
        ];
        yield 'ls <path:int> ...' => [
            function (): void {
                $cli = new class(
                    new #[Cmd()] class {
                        /** @var list<int> */
                        #[Arg(type: 'int')]
                        public array $paths = [];
                    },
                ) {
                    public function __construct(
                        public object $ls,
                    ) {
                    }
                };

                self::parse($cli, ['ls', '1', '2']);
                /** @phpstan-ignore-next-line */
                self::assertSame([1, 2], $cli->ls->paths);
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
                    ) {
                    }
                };

                self::parse($cli, ['--flag', 'ls', 'foo.php', 'bar.php']);
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
                        public bool $flag = false;

                        /** @var list<string> */
                        #[Arg()]
                        public array $paths = [];
                    },
                ) {
                    public function __construct(
                        public object $ls,
                    ) {
                    }
                };

                self::parse($cli, ['ls', '--flag', 'foo.php', 'bar.php']);
                /** @phpstan-ignore-next-line */
                self::assertEquals(true, $cli->ls->flag);
                /** @phpstan-ignore-next-line */
                self::assertEquals(['foo.php', 'bar.php'], $cli->ls->paths);
            },
        ];
        yield 'ls <path> --flag' => [
            function (): void {
                $cli = new class(
                    new #[Cmd()] class {
                        #[Arg()]
                        public string $path;

                        #[Opt()]
                        public bool $flag = false;
                    },
                ) {
                    public function __construct(
                        public object $ls,
                    ) {
                    }
                };

                self::parse($cli, ['ls', 'bar.php', '--flag', ]);
                /** @phpstan-ignore-next-line */
                self::assertEquals(true, $cli->ls->flag);
                /** @phpstan-ignore-next-line */
                self::assertEquals('bar.php', $cli->ls->path);
            },
        ];

        yield 'ls <path> --flag with multiple commands' => [
            function (): void {
                $cli = new class(
                    new #[Cmd()] class {
                        #[Arg()]
                        public string $path;
                    },
                    new #[Cmd()] class {
                        #[Arg()]
                        public string $path;

                        #[Opt()]
                        public bool $flag = false;
                    },
                ) {
                    public function __construct(
                        public object $rm,
                        public object $ls,
                    ) {
                    }
                };

                self::parse($cli, ['ls', 'bar.php', '--flag', ]);
                /** @phpstan-ignore-next-line */
                self::assertEquals(true, $cli->ls->flag);
                /** @phpstan-ignore-next-line */
                self::assertEquals('bar.php', $cli->ls->path);
            },
        ];
        yield 'user <name> set-phone 0123412341234 --home' => [
            function (): void {
                $cli = new class(
                    new #[Cmd(name: 'user')] class(
                        '',
                        new #[Cmd(name: 'set-phone')] class {
                            #[Arg()]
                            public int $number;

                            #[Opt()]
                            public bool $home;
                        }
                    ) {
                        public function __construct(
                            #[Arg()]
                            public string $name,
                            public object $setPhone,
                        ) {
                        }
                    },
                ) {
                    public function __construct(
                        public object $user,
                    ) {
                    }
                };

                self::parse($cli, ['user', 'daniel', 'set-phone', '0123412341234', '--home']);
                /** @phpstan-ignore-next-line */
                self::assertEquals(true, $cli->user->setPhone->home);
                /** @phpstan-ignore-next-line */
                self::assertEquals('daniel', $cli->user->name);
            },
        ];
    }

    public function testExample(): void
    {
        // shell rm [-f] [-r] <paths> ...
        // shell ls [<paths> ...]
        $cli = new #[Cmd('My App', help: 'Application to list and remove files')] class(
            new #[Cmd('rm', help: 'Remove files')] class {
                #[Opt(help: 'Force removal')]
                public bool $force = false;

                #[Opt(help: 'Recursively remove files', short: 'r')]
                public bool $recursive = false;

                /** @var list<string> */
                #[Arg(help: 'Paths to remove', name: 'path', /** type: 'path' */)]
                public array $paths = [];
            },
            new #[Cmd('ls', help: 'List files')] class {
                /** @var list<string> */
                #[Arg(help: 'Paths to list', name: 'path', /** type: 'path' */)]
                public array $paths = [];
            },
        ) {
            public function __construct(
                public object $rmCmd,
                public object $lsCmd,
            ) {
            }
        };

        $cmd = self::parse($cli, ['rm', '--force', '-r', 'path1.php', 'path2.php']);
        /** @phpstan-ignore-next-line */
        self::assertTrue($cmd->force);
        /** @phpstan-ignore-next-line */
        self::assertTrue($cmd->recursive);
        /** @phpstan-ignore-next-line */
        self::assertSame(['path1.php', 'path2.php'], $cli->rmCmd->paths);
        $cmd = self::parse($cli, ['ls', 'path1.php', 'path2.php']);
        /** @phpstan-ignore-next-line */
        self::assertSame(['path1.php', 'path2.php'], $cmd->paths);
    }

    /**
     * @param list<string> $args
     */
    private static function parse(object $target, array $args): object
    {
        $definition = (new Loader())->load($target);
        return (new Parser())->parse($definition, $target, $args)[1];
    }
}
