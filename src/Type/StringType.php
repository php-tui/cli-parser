<?php

namespace PhpTui\CliParser\Type;

/**
 * @implements Type<string>
 */
final class StringType implements Type
{
    public function toString(): string
    {
        return 'string';
    }

    public function parse(string $value): mixed
    {
        return $value;
    }
}
