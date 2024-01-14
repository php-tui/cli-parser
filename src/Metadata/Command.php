<?php

namespace PhpTui\CliParser\Metadata;

final class Command
{
    /**
     * @param array<int,Argument> $arguments
     */
    public function __construct(
        string $name,
        array $arguments,
        array $options,
    ) {
    }

}
