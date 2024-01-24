<?php

namespace PhpTui\CliParser\Metadata;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<ArgumentDefinition>
 */
final class ArgumentDefinitions implements IteratorAggregate, Countable
{
    /**
     * @param ArgumentDefinition[] $arguments
     */
    public function __construct(private readonly array $arguments)
    {
    }

    public function getArgument(string $name):?ArgumentDefinition
    {
        foreach ($this->arguments as $argument) {
            if ($argument->name === $name) {
                return $argument;
            }
        }

        return null;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->arguments);
    }

    public function count(): int
    {
        return count($this->arguments);
    }

    /**
     * @return ArgumentDefinition[]
     */
    public function toArray(): array
    {
        return $this->arguments;
    }
}
