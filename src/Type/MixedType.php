<?php

namespace PhpTui\CliParser\Type;

/**
 * @implements Type<mixed>
 */
final class MixedType implements Type
{
    public function toString(): string
    {
        return 'mixed';
    }

    public function parse(string $value): mixed
    {
        return $value;
    }
}
