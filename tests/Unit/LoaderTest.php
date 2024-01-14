<?php

namespace PhpTui\CliParser\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\Attribute\Arg;
use PhpTui\CliParser\Loader;
use PhpTui\CliParser\Metadata\Argument;
use PhpTui\CliParser\Metadata\Command;
use PhpTui\CliParser\Type\StringType;

final class LoaderTest extends TestCase
{
    public function testPositionalArgument(): void
    {
        $target = new class {
            #[Arg()]
            public string $foobar;
        };

        self::assertEquals(new Command(
            name: Loader::ROOT_NAME,
            arguments: [
                new Argument(name: 'foobar', type: new StringType()),
            ],
            options: [],
        ), $this->load($target));
    }

    private function load(object $object): Command
    {
        return (new Loader())->load($object);
    }
}
