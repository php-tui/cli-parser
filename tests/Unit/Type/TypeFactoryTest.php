<?php

namespace PhpTui\CliParser\Tests\Unit\Type;

use Closure;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PhpTui\CliParser\Type\IntegerType;
use PhpTui\CliParser\Type\MixedType;
use PhpTui\CliParser\Type\StringType;
use PhpTui\CliParser\Type\Type;
use PhpTui\CliParser\Type\TypeFactory;
use PhpTui\CliParser\Type\UnionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;

final class TypeFactoryTest extends TestCase
{
    /**
     * @param Closure():(ReflectionType|null) $factory
     */
    #[DataProvider('provideFromReflectionType')]
    public function testFromReflectionType(Closure $factory, Type $expected): void
    {
        self::assertEquals($expected, TypeFactory::fromReflectionType($factory()));
    }

    public static function provideFromReflectionType(): Generator
    {
        yield 'null => mixed' => [
            function () {
                return null;
            },
            new MixedType(),
        ];

        yield 'string => string' => [
            function () {
                return (new ReflectionProperty(
                    new class {public string $foo;},
                    'foo'
                ))->getType();
            },
            new StringType(),
        ];

        yield 'union => union' => [
            function () {
                return (new ReflectionProperty(
                    new class {public string|int $foo;},
                    'foo'
                ))->getType();
            },
            new UnionType(new StringType(), new IntegerType()),
        ];
    }
}
