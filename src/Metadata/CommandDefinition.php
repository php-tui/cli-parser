<?php

namespace PhpTui\CliParser\Metadata;

final class CommandDefinition extends AbstractCommandDefinition
{
    public function __construct(
        string $name,
        public readonly string $propertyName,
        ArgumentDefinitions $arguments = null,
        OptionDefinitions $options = null,
        CommandDefinitions $commands = null,
        ?string $help = null,
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
