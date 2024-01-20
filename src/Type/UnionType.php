<?php

namespace PhpTui\CliParser\Type;

/**
 * @implements Type<mixed>
 */
final class UnionType implements Type
{
    /**
     * @var Type<mixed>[]
     */
    public array $types;

    /**
     * @param Type<mixed> ...$types
     */
    public function __construct(Type ...$types)
    {
        $this->types = $types;
    }

    public function toString(): string
    {
        return implode('|', array_map(fn (Type $type) => $type->toString(), $this->types));
    }

    public function parse(string $value): mixed
    {
        return $value;
    }
}
