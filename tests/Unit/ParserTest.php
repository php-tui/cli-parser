<?php

namespace PhpTui\CliParser\Tests\Unit;

use Closure;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\Attribute\Arg;
use PhpTui\CliParser\Attribute\Opt;
use PhpTui\CliParser\Parser;

final class ParserTest extends TestCase
{
    #[DataProvider('provideArguments')]
    #[DataProvider('provideOptions')]
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
            function () {
                $target = new class {
                    #[Arg()]
                    public ?string $foobar = null;
                };
                self::parse($target, ['cmd', 'foobar']);
                self::assertEquals('foobar', $target->foobar);
            },
        ];
        yield '<foobar> <barfoo>' => [
            function () {
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
        yield '<foobar> <barfoo> --options=<value>' => [
            function () {
                $target = new class {
                    #[Arg()]
                    public ?string $foobar = null;
                    #[Opt()]
                    public ?string $option = null;
                    #[Arg()]
                    public ?string $barfoo = null;
                };
                self::parse($target, ['cmd', 'foobar', '--option=foo', 'barfoo']);

                self::assertEquals('foobar', $target->foobar);
                self::assertEquals('barfoo', $target->barfoo);
                self::assertEquals('foo', $target->option);
            }
        ];
        yield '<foobar> where foobar has spaces' => [
            function () {
                $target = new class {
                    #[Arg()]
                    public ?string $foobar = null;
                };
                self::parse($target, ['cmd', 'foobar barfoo']);

                self::assertEquals('foobar barfoo', $target->foobar);
            }
        ];
        yield '[<foobar>] optional argument omitted' => [
            function () {
                $target = new class {
                    #[Arg(required: false)]
                    public string $foobar = 'bar';
                };
                self::parse($target, ['cmd']);

                self::assertEquals('bar', $target->foobar);
            }
        ];
        yield 'all supported types' => [
            function () {
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
            function () {
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
            function () {
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
            function () {
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
            function () {
                $target = new class {
                    #[Opt()]
                    public ?string $greeting = null;
                };
                self::parse($target, ['cmd', '--greeting=hello world']);

                self::assertSame("hello world", $target->greeting);
            },
        ];
        yield '-g"hello world" short option' => [
            function () {
                $target = new class {
                    #[Opt(short:"g")]
                    public ?string $greeting = null;
                };
                self::parse($target, ['cmd', '-ghello world']);

                self::assertSame("hello world", $target->greeting);
            },
        ];
    }

    /**
     * @param list<string> $args
     */
    private static function parse(object $target, array $args): void
    {
        (new Parser())->parse($target, $args);
    }
}
