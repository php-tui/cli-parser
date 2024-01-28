<?php

namespace PhpTui\CliParser\Attribute;

use Attribute;

#[Attribute]
final class App
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $author = null,
        public readonly ?string $version = null,
        public readonly ?string $help = null,
    ) {
    }
}
