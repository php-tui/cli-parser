<?php

namespace PhpTui\CliParser\Metadata;

final class Command implements ArgumentLike
{
    /**
     * @param list<ArgumentLike> $arguments
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

}
