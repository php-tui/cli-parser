<?php

namespace PhpTui\CliParser\Type;

use BadMethodCallException;

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

    /**
     * @return IType
     */
    public function itemType(): Type
    {
        return $this->type;
    }

    public function parse(string $value): mixed
    {
        throw new BadMethodCallException('List type does not support parsing');
    }
}
