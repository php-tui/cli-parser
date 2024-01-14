<?php

namespace PhpTui\CliParser\Attribute;

use Attribute;

#[Attribute]
final class Cmd
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $help = null,
    ) {
    }
}
