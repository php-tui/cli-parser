<?php

namespace PhpTui\CliParser\Attribute;

use Attribute;

#[Attribute]
final class Opt
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $short = null,
        public readonly ?string $long = null,
        public readonly ?string $help = null
    ) {
    }

}
