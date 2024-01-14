<?php

namespace PhpTui\CliParser\Metadata;

final class Command implements ArgumentLike
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
        return array_filter($this->arguments, fn (ArgumentLike $a) => $a instanceof Command);
    }
    /**
     * @return Argument[]
     */
    public function arguments(): array
    {
        return array_filter($this->arguments, fn (ArgumentLike $a) => $a instanceof Argument);
    }

    /**
     * @return array<string,Option>
     */
    public function optionsKeyedByCliName(): array
    {
        return array_combine(array_map(
            fn (Option $option) => $option->parseName,
            $this->options
        ), array_values($this->options));
    }

}
