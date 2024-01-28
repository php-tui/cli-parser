<?php

namespace PhpTui\CliParser\Tests\Unit\Printer;

use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\Metadata\ApplicationDefinition;
use PhpTui\CliParser\Metadata\ArgumentDefinition;
use PhpTui\CliParser\Metadata\ArgumentDefinitions;
use PhpTui\CliParser\Metadata\CommandDefinition;
use PhpTui\CliParser\Metadata\CommandDefinitions;
use PhpTui\CliParser\Metadata\OptionDefinition;
use PhpTui\CliParser\Metadata\OptionDefinitions;
use PhpTui\CliParser\Printer\AsciiPrinter;
use PhpTui\CliParser\Type\IntegerType;
use PhpTui\CliParser\Type\StringType;

final class AsciiPrinterTest extends TestCase
{
    public function testPrinter(): void
    {
        $printed = (new AsciiPrinter())->print(
            new ApplicationDefinition(
                name: 'root',
                arguments: new ArgumentDefinitions([
                    new ArgumentDefinition(
                        name: 'path',
                        type: new StringType(),
                        help: 'Path to the fooble you want to modify',
                    ),
                ]),
                commands: new CommandDefinitions([
                    new CommandDefinition(
                        name: 'operate',
                        propertyName: 'operate',
                        help: 'Sub command for operations',
                        options: new OptionDefinitions([
                            new OptionDefinition(
                                name: 'left',
                                type: new StringType(),
                                help: 'Operate to the left',
                            ),
                        ])
                    ),
                ]),
                options: new OptionDefinitions([
                    new OptionDefinition(
                        name: 'change-directory',
                        type: new IntegerType(),
                        short: 'c',
                        help: 'Change the directory to another one',
                    )
                ]),
                help: null
            ),
        );

        self::assertEquals(<<<'EOT'
            root <path> [--change-directory|-c] 

            Options:

              -c, --change-directory  Change the directory to another one (integer)

            Commands:

              operate [--left] Sub command for operations
            EOT, str_replace("\t", ' ', $printed));
    }
}
