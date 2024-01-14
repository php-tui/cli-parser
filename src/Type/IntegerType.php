<?php

namespace PhpTui\CliParser\Type;

/**
 * @implements Type<int>
 */
final class IntegerType implements Type
{
    public function toString(): string
    {
        return 'integer';
    }

    public function parse(string $value): mixed
    {
        return intval($value);
    }
}
