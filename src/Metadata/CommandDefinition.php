<?php

namespace PhpTui\CliParser\Metadata;

use PhpTui\CliParser\Error\ParseError;

final class CommandDefinition implements ArgumentLike
{
    /**
     * @var array<string,OptionDefinition>
     */
    private readonly array $optionsByName;

    /**
     * @var array<string,OptionDefinition>
     */
    private readonly array $optionsByShort;

    /**
     * @var list<OptionDefinition>
     */
    private readonly array $options;

    /**
     * @param list<ArgumentDefinition|CommandDefinition> $arguments
     * @param list<OptionDefinition> $options
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $propertyName = null,
        public readonly array $arguments = [],
        array $options = [],
        public readonly ?string $help = null,
    ) {
        $optionsByName = [];
        $optionsByShort = [];
        foreach ($options as $option) {
            $optionsByName[$option->parseName] = $option;
            if ($option->short !== null) {
                $optionsByShort[$option->short] = $option;
            }
        }
        $this->optionsByName = $optionsByName;
        $this->optionsByShort = $optionsByShort;
        $this->options = $options;
    }
    /**
     * @return CommandDefinition[]
     */
    public function commands(): array
    {
        return array_filter($this->arguments, fn (ArgumentLike $a) => $a instanceof CommandDefinition);
    }
    /**
     * @return ArgumentDefinition[]
     */
    public function arguments(): array
    {
        return array_filter(
            $this->arguments,
            fn (ArgumentLike $a) => $a instanceof ArgumentDefinition
        );
    }

    public function getCommand(string $name):?CommandDefinition
    {
        foreach ($this->commands() as $command) {
            if ($command->name === $name) {
                return $command;
            }
        }

        return null;
    }

    public function getOption(string $name): OptionDefinition
    {
        if (isset($this->optionsByName[$name])) {
            return $this->optionsByName[$name];
        }

        throw new ParseError(sprintf(
            'Unknown option --%s for command "%s", known options: %s',
            $name,
            $this->name,
            implode(', ', array_keys($this->optionsByName))
        ));
    }

    public function options(): array
    {
        return $this->options;
    }

    public function getShortOption(string $name): OptionDefinition
    {
        if (isset($this->optionsByShort[$name])) {
            return $this->optionsByShort[$name];
        }

        throw new ParseError(sprintf(
            'Unknown short option -%s for command "%s", known options: %s',
            $name,
            $this->name,
            implode(', ', array_keys($this->optionsByShort))
        ));
    }

}
