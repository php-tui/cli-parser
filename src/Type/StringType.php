<?php

namespace PhpTui\CliParser\Type;

final class StringType implements Type
{
    public function toString(): string
    {
        return 'string';
    }
}
