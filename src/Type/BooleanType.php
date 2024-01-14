<?php

namespace PhpTui\CliParser\Type;

class BooleanType implements Type
{
    public function toString(): string
    {
        return 'boolean';
    }
}
