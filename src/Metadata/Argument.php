<?php

namespace PhpTui\CliParser\Metadata;

use PhpTui\CliParser\Type\Type;

final class Argument
{
    public function __construct(
        public string $name,
        public Type $type
    ) {
    }

}
