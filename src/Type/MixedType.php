<?php

namespace PhpTui\CliParser\Type;

final class MixedType implements Type
{
    public function toString(): string
    {
        return 'mixed';
    }
}