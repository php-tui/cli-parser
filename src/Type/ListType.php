<?php

namespace PhpTui\CliParser\Type;

/**
 * @template IType of Type
 * @implements Type<IType>
 */
final class ListType implements Type
{
    /**
     * @param IType $type
     */
    public function __construct(private Type $type)
    {
    }

    public function toString(): string
    {
        return sprintf('list<%s>', $this->type->toString());
    }

    public function parse(string $value): mixed
    {
        throw new \Exception('nope');
    }
}
