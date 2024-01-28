<?php

namespace PhpTui\CliParser\Metadata;

class ApplicationDefinition extends AbstractCommandDefinition
{
    public function __construct(
        string $name,
        ArgumentDefinitions $arguments = null,
        OptionDefinitions $options = null,
        CommandDefinitions $commands = null,
        ?string $help = null,
        public readonly ?string $author = null,
        public readonly ?string $version = null,
    ) {
        parent::__construct(
            $name,
            $arguments,
            $options,
            $commands,
            $help
        );
    }
}
