<?php

namespace PhpTui\CliParser\Tests\Unit\Metadata;

use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\Attribute\App;
use PhpTui\CliParser\Attribute\Arg;
use PhpTui\CliParser\Attribute\Cmd;
use PhpTui\CliParser\Attribute\Opt;
use PhpTui\CliParser\Metadata\Loader;
use PhpTui\CliParser\Metadata\ApplicationDefinition;
use PhpTui\CliParser\Metadata\ArgumentDefinition;
use PhpTui\CliParser\Metadata\ArgumentDefinitions;
use PhpTui\CliParser\Metadata\CommandDefinition;
use PhpTui\CliParser\Metadata\CommandDefinitions;
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

    public function testApplicationVersionAndAuthor(): void
    {
        $this->assertRoot(
            new #[App(author: 'Daniel Leech', version: '1.0')] class {
            },
            author: 'Daniel Leech',
            version: '1.0',
            options: [
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
            commands: [
                new CommandDefinition(
                    name: 'subCommand',
                    help: 'This is a sub-command',
                    propertyName: 'subCommand',
                    arguments: new ArgumentDefinitions([
                        new ArgumentDefinition(name: 'url', type: new StringType()),
                    ])
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
     * @param array<int,ArgumentDefinition> $arguments
     * @param array<int,CommandDefinition> $commands
     * @param array<int,OptionDefinition> $options
     */
    private function assertRoot(
        object $target,
        array $arguments = [],
        array $commands = [],
        array $options = [],
        ?string $help = null,
        ?string $propertyName = null,
        ?string $author = null,
        ?string $version = null,
    ): void {
        self::assertEquals(new ApplicationDefinition(
            name: Loader::ROOT_NAME,
            author: $author,
            version:$version,
            arguments: new ArgumentDefinitions($arguments),
            commands: new CommandDefinitions($commands),
            options: new OptionDefinitions($options),
            help: $help,
        ), $this->load($target));
    }

    private function load(object $object): ApplicationDefinition
    {
        return (new Loader())->load($object);
    }
}
