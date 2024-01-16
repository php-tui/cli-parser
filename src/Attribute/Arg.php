<?php

namespace PhpTui\CliParser\Attribute;

use Attribute;

#[Attribute]
final class Arg
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $help = null,
        public readonly bool $required = true,
    ) {
    }

}
