<?php

namespace PhpTui\CliParser\Type;

class IntegerType implements Type
{
    public function toString(): string
    {
        return 'integer';
    }
}
