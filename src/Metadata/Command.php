<?php

namespace PhpTui\CliParser\Metadata;

final class Command
{
    /**
     * @param array<int,Argument> $arguments
     */
    public function __construct(
        public readonly string $name,
        public readonly array $arguments,
        public readonly array $options,
    ) {
    }

}
