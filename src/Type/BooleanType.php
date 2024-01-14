<?php

namespace PhpTui\CliParser\Type;

/**
 * @implements Type<bool>
 */
final class BooleanType implements Type
{
    public function toString(): string
    {
        return 'boolean';
    }

    public function parse(string $value): mixed
    {
        return true;
    }
}
