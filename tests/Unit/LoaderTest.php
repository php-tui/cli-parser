<?php

namespace PhpTui\CliParser\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\Attribute\Arg;
use PhpTui\CliParser\Attribute\Cmd;
use PhpTui\CliParser\Attribute\Opt;
use PhpTui\CliParser\Loader;
use PhpTui\CliParser\Metadata\Argument;
use PhpTui\CliParser\Metadata\Command;
use PhpTui\CliParser\Metadata\Option;
use PhpTui\CliParser\Type\IntegerType;
use PhpTui\CliParser\Type\StringType;

final class LoaderTest extends TestCase
{
    public function testPositionalArgument(): void
    {
        $this->assertRoot(
            new class {
                #[Arg()]
                public string $foobar;
            },
            arguments: [
                new Argument(
                    name: 'foobar',
                    type: new StringType()
                )
            ]
        );
    }

    public function testPositionalArguments(): void
    {
        $this->assertRoot(
            new class {
                #[Arg()]
                public string $foobar;

                #[Arg()]
                public int $barfoo;
            },
            arguments: [
                new Argument(name: 'foobar', type: new StringType()),
                new Argument(name: 'barfoo', type: new IntegerType()),
            ]
        );
    }

    public function testOption(): void
    {
        $this->assertRoot(
            new class {
                #[Opt()]
                public string $foobar;
            },
            options: [
                new Option(
                    name: 'foobar',
                    type: new StringType(),
                ),
            ]
        );
    }

    public function testOptionOverrideName(): void
    {
        $this->assertRoot(
            new class {
                #[Opt(name: 'foo-bar')]
                public string $foobar;
            },
            options: [
                new Option(
                    name: 'foobar',
                    parseName: 'foo-bar',
                    type: new StringType(),
                ),
            ]
        );
    }

    public function testOptionShortName(): void
    {
        $this->assertRoot(
            new class {
                #[Opt(short: 's')]
                public string $foobar;
            },
            options: [
                new Option(
                    name: 'foobar',
                    short: 's',
                    type: new StringType(),
                ),
            ]
        );
    }

    public function testArgAndOptHelp(): void
    {
        $this->assertRoot(
            new class {
                #[Arg(help: 'This is some arg help')]
                public string $barfoo;

                #[Opt(help: 'This is some opt help')]
                public string $foobar;
            },
            arguments: [
                new Argument(
                    name: 'barfoo',
                    type: new StringType(),
                    help: 'This is some arg help',
                ),
            ],
            options: [
                new Option(
                    name: 'foobar',
                    type: new StringType(),
                    help: 'This is some opt help',
                ),
            ]
        );
    }

    public function testSubCommand(): void
    {
        $this->assertRoot(
            new class {
                public ?object $subCommand = null;
                public function __construct(
                    #[Opt(help: 'This is some opt help')]
                    public string $foobar = '',
                ) {
                    $this->subCommand = new #[Cmd(help: 'This is a sub-command')] class {
                        #[Arg()]
                        public string $url;
                    };
                }
            },
            arguments: [
                new Command(
                    name: 'subCommand',
                    help: 'This is a sub-command',
                    arguments: [
                        new Argument(name: 'url', type: new StringType()),
                    ]
                ),
            ],
            options: [
                new Option(
                    name: 'foobar',
                    type: new StringType(),
                    help: 'This is some opt help',
                ),
            ]
        );
    }

    /**
     * @param array<int,Argument|Command> $arguments
     * @param array<int,Option> $options
     */
    private function assertRoot(object $target, array $arguments = [], array $options = []): void
    {
        self::assertEquals(new Command(
            name: Loader::ROOT_NAME,
            arguments: $arguments,
            options: $options,
        ), $this->load($target));
    }

    private function load(object $object): Command
    {
        return (new Loader())->load($object);
    }
}
