<?php

namespace PhpTui\CliParser\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\Attribute\Arg;
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
                new Argument(name: 'foobar', type: new StringType())
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
                    name: 'foobar'
                ),
            ]
        );
    }

    /**
     * @param array<int,Argument> $arguments
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
