<?php

namespace PhpTui\CliParser\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\Attribute\Arg;
use PhpTui\CliParser\Attribute\Cmd;
use PhpTui\CliParser\Attribute\Opt;
use PhpTui\CliParser\Loader;
use PhpTui\CliParser\Metadata\ArgumentDefinition;
use PhpTui\CliParser\Metadata\CommandDefinition;
use PhpTui\CliParser\Metadata\OptionDefinition;
use PhpTui\CliParser\Metadata\OptionDefinitions;
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
                new ArgumentDefinition(
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
                new ArgumentDefinition(name: 'foobar', type: new StringType()),
                new ArgumentDefinition(name: 'barfoo', type: new IntegerType()),
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
                new OptionDefinition(
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
                new OptionDefinition(
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
                new OptionDefinition(
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
                new ArgumentDefinition(
                    name: 'barfoo',
                    type: new StringType(),
                    help: 'This is some arg help',
                ),
            ],
            options: [
                new OptionDefinition(
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
            new #[Cmd(help: 'This is the main command')] class {
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
            help: 'This is the main command',
            arguments: [
                new CommandDefinition(
                    name: 'subCommand',
                    help: 'This is a sub-command',
                    propertyName: 'subCommand',
                    arguments: [
                        new ArgumentDefinition(name: 'url', type: new StringType()),
                    ]
                ),
            ],
            options: [
                new OptionDefinition(
                    name: 'foobar',
                    type: new StringType(),
                    help: 'This is some opt help',
                ),
            ]
        );
    }

    /**
     * @param array<int,ArgumentDefinition|CommandDefinition> $arguments
     * @param array<int,OptionDefinition> $options
     */
    private function assertRoot(object $target, array $arguments = [], array $options = [], ?string $help = null, ?string $propertyName = null): void
    {
        self::assertEquals(new CommandDefinition(
            name: Loader::ROOT_NAME,
            propertyName: $propertyName,
            arguments: $arguments,
            options: new OptionDefinitions($options),
            help: $help,
        ), $this->load($target));
    }

    private function load(object $object): CommandDefinition
    {
        return (new Loader())->load($object);
    }
}
