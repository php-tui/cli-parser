<?php

namespace PhpTui\CliParser\Metadata;

use PhpTui\CliParser\Type\Type;

final class Argument implements ArgumentLike
{
    public function __construct(
        public string $name,
        public Type $type,
        public ?string $help = null,
    ) {
    }

}
