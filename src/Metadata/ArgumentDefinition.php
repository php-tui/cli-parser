<?php

namespace PhpTui\CliParser\Metadata;

use PhpTui\CliParser\Type\Type;

final class ArgumentDefinition
{
    /**
     * @template TType
     * @param Type<TType> $type
     */
    public function __construct(
        public string $name,
        public Type $type,
        public ?string $help = null,
        public bool $required = true
    ) {
    }

}
