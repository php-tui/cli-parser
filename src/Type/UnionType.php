<?php

namespace PhpTui\CliParser\Type;

final class UnionType implements Type
{
    /**
     * @var Type[]
     */
    public array $types;

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
