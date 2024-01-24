<?php

namespace PhpTui\CliParser\Metadata;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<CommandDefinition>
 */
final class CommandDefinitions implements IteratorAggregate, Countable
{
    /**
     * @param CommandDefinition[] $commands
     */
    public function __construct(private array $commands)
    {
    }

    public function getCommand(string $name):?CommandDefinition
    {
        foreach ($this->commands as $command) {
            if ($command->name === $name) {
                return $command;
            }
        }

        return null;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->commands);
    }

    public function count(): int
    {
        return count($this->commands);
    }
}
