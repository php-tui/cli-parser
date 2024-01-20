<?php

namespace PhpTui\CliParser\Tests\Unit\Printer;

use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\Metadata\Argument;
use PhpTui\CliParser\Metadata\Command;
use PhpTui\CliParser\Metadata\Option;
use PhpTui\CliParser\Printer\AsciiPrinter;
use PhpTui\CliParser\Type\IntegerType;
use PhpTui\CliParser\Type\StringType;

final class AsciiPrinterTest extends TestCase
{
    public function testPrinter(): void
    {
        $printed = (new AsciiPrinter())->print(
            new Command(
                name: 'root',
                arguments: [
                    new Argument(
                        name: 'path',
                        type: new StringType(),
                        help: 'Path to the fooble you want to modify',
                    ),
                    new Command(
                        name: 'operate',
                        help: 'Sub command for operations',
                        options: [
                            new Option(
                                name: 'left',
                                type: new StringType(),
                                help: 'Operate to the left',
                            ),
                        ]
                    ),
                ],
                options: [
                    new Option(
                        name: 'change-directory',
                        type: new IntegerType(),
                        short: 'c',
                        help: 'Change the directory to another one',
                    )
                ],
                help: null
            ),
        );

        self::assertEquals(<<<'EOT'
            root <path> [--change-directory|-c]

            Options:

              -c, --change-directory  Change the directory to another one (integer)

            Commands:

              operate [--left]
            EOT, str_replace("\t", ' ', $printed));
    }
}
