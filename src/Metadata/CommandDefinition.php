<?php

namespace PhpTui\CliParser\Metadata;

final class CommandDefinition
{
    private OptionDefinitions $options;

    private ArgumentDefinitions $arguments;

    private CommandDefinitions $commands;

    public function __construct(
        public readonly string $name,
        public readonly ?string $propertyName = null,
        ArgumentDefinitions $arguments = null,
        OptionDefinitions $options = null,
        CommandDefinitions $commands = null,
        public readonly ?string $help = null,
    ) {
        $this->options = $options ?: new OptionDefinitions([]);
        $this->arguments = $arguments ?: new ArgumentDefinitions([]);
        $this->commands = $commands ?: new CommandDefinitions([]);
    }

    public function commands(): CommandDefinitions
    {
        return $this->commands;
    }

    public function arguments(): ArgumentDefinitions
    {
        return $this->arguments;
    }

    public function options(): OptionDefinitions
    {
        return $this->options;
    }
}
