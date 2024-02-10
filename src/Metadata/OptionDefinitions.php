<?php

namespace PhpTui\CliParser\Metadata;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use PhpTui\CliParser\Error\ParseError;
use Traversable;

/**
 * @implements IteratorAggregate<OptionDefinition>
 */
final class OptionDefinitions implements IteratorAggregate, Countable
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
     * @var OptionDefinition[]
     */
    private readonly array $options;

    /**
     * @param OptionDefinition[] $options
     */
    public function __construct(array $options)
    {
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

    public function getOrNull(string $name): ?OptionDefinition
    {
        if (isset($this->optionsByName[$name])) {
            return $this->optionsByName[$name];
        }

        return null;
    }

    public function get(string $name): OptionDefinition
    {
        $option = $this->getOrNull($name);

        if ($option) {
            return $this->optionsByName[$name];
        }

        throw new ParseError(sprintf(
            'Unknown option --%s, known options: %s',
            $name,
            implode(', ', array_keys($this->optionsByName))
        ));
    }

    public function shortOption(string $name): OptionDefinition
    {
        if (isset($this->optionsByShort[$name])) {
            return $this->optionsByShort[$name];
        }

        throw new ParseError(sprintf(
            'Unknown short option -%s, known options: %s',
            $name,
            implode(', ', array_keys($this->optionsByShort))
        ));
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->options);
    }

    public function count(): int
    {
        return count($this->options);
    }
}
