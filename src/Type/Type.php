<?php

namespace PhpTui\CliParser\Type;

/**
 * @template-covariant TParseType
 */
interface Type
{
    public function toString(): string;

    /**
     * @return TParseType
     */
    public function parse(string $value): mixed;
}
