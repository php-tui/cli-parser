<?php

namespace PhpTui\CliParser\Type;

use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use RuntimeException;

final class TypeFactory
{
    /**
     * @return Type<mixed>
     */
    public static function fromReflectionType(ReflectionType|null $type): Type
    {
        if (null === $type) {
            return new MixedType();
        }

        if ($type instanceof ReflectionUnionType) {
            return new UnionType(
                ...array_map(
                    fn (ReflectionType|null $t) => self::fromReflectionType($t),
                    $type->getTypes(),
                )
            );
        }

        if (!$type instanceof ReflectionNamedType) {
            throw new RuntimeException(sprintf(
                'Unknown reflection type "%s"',
                $type::class
            ));
        }

        return self::fromString($type->getName());
    }

    /**
     * @return Type<mixed>
     */
    public static function fromString(?string $name): Type
    {
        return match ($name) {
            null => new MixedType(),
            'string' => new StringType(),
            'int' => new IntegerType(),
            'bool' => new BooleanType(),
            'float' => new FloatType(),
            'array' => new ListType(new StringType()),
            default => throw new RuntimeException(sprintf(
                'Do not know how to parse type "%s"',
                $name
            )),
        };
    }
}
