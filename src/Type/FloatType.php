<?php

namespace PhpTui\CliParser\Type;

/**
 * @implements Type<float>
 */
final class FloatType implements Type
{
    public function toString(): string
    {
        return 'float';
    }

    public function parse(string $value): mixed
    {
        return floatval($value);
    }
}
