<?php

namespace PhpTui\CliParser\Metadata;

final class CommandDefinition implements ArgumentLike
{
    /**
     * @var OptionDefinitions
     */
    private OptionDefinitions $options;

    /**
     * @param list<ArgumentDefinition|CommandDefinition> $arguments
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $propertyName = null,
        public readonly array $arguments = [],
        OptionDefinitions $options = null,
        public readonly ?string $help = null,
    ) {
        $this->options = $options ?: new OptionDefinitions([]);
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

    public function options(): OptionDefinitions
    {
        return $this->options;
    }
}
