<?php

namespace PhpTui\CliParser\Metadata;

final class CommandDefinition implements ArgumentLike
{
    /**
     * @param list<Argument|Command> $arguments
     * @param list<Option> $options
     */
    public function __construct(
        public readonly string $name,
        public readonly array $arguments = [],
        public readonly array $options = [],
        public readonly ?string $help = null,
    ) {
    }
    /**
     * @return Command[]
     */
    public function commands(): array
    {
        return array_filter($this->arguments, fn (ArgumentLike $a) => $a instanceof CommandDefinition);
    }
    /**
     * @return Argument[]
     */
    public function arguments(): array
    {
        return array_filter(
            $this->arguments,
            fn (ArgumentLike $a) => $a instanceof ArgumentDefinition
        );
    }

    /**
     * @return array<string,Option>
     */
    public function optionsKeyedByName(): array
    {
        return array_merge($this->optionsKeyedByLongName(), $this->optionsKeyedByShortName());
    }

    /**
     * @return array<string,Option>
     */
    public function optionsKeyedByLongName(): array
    {
        return array_combine(array_map(
            fn (OptionDefinition $option) => $option->parseName,
            $this->options
        ), array_values($this->options));
    }
    /**
     * @return array<string,Option>
     */
    public function optionsKeyedByShortName(): array
    {
        $shortOptions = array_filter(
            $this->options,
            fn (OptionDefinition $short) => $short->short !== null,
        );

        return array_combine(array_map(
            fn (OptionDefinition $option) => (string)$option->short,
            $shortOptions,
        ), array_values($shortOptions));
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

}
