<?php

namespace PhpTui\CliParser;

use PhpTui\CliParser\Error\ParseError;
use PhpTui\CliParser\Metadata\Argument;
use PhpTui\CliParser\Metadata\ArgumentDefinition;
use PhpTui\CliParser\Metadata\CommandDefinition;
use PhpTui\CliParser\Type\BooleanType;
use PhpTui\CliParser\Type\ListType;
use RuntimeException;

final class Parser
{
    private const T_ARG = 'T_ARG';
    private const T_OPT = 'T_OPT';
    private const T_OPT_FLAG = 'T_FLAG';
    private const T_OPT_SHORT = 'T_OPT_SHORT';
    private const T_OPT_SHORT_FLAG = 'T_OPT_SHORT_FLAG';

    private Loader $loader;

    public function __construct(Loader $loader = null)
    {
        $this->loader = $loader ?: new Loader();
    }

    /**
     * @param string[] $args
     */
    public function parse(object $target, array $args): void
    {
        $commandDefinition = $this->loader->load($target);
        $argumentDefinitions = $commandDefinition->arguments();
        $commandDefinitions = $commandDefinition->commands();

        $longOptions = [];

        while ($arg = array_shift($args)) {
            $parsed = $this->parseArgument($arg);

            $type = $parsed[0];
            $value = $parsed[1];
            $name = $parsed[2] ?? null;

            if ($type === self::T_ARG) {
                $stopParsing = $this->mapArgument(
                    $commandDefinition,
                    $target,
                    $args,
                    $argumentDefinitions,
                    $arg
                );
                if ($stopParsing) {
                    return;
                }
                continue;
            }

            if ($type === self::T_OPT_FLAG) {
                $this->mapFlag($commandDefinition, $target, $value);
                continue;
            }
            if ($type === self::T_OPT) {
                $this->mapOption($commandDefinition, $target, $name ?? '', $value);
                continue;
            }
            if ($type === self::T_OPT_SHORT) {
                $this->mapShortOption($commandDefinition, $target, $name ?? '', $value);
                continue;
            }

            throw new RuntimeException(sprintf(
                'Do not know how to map argument of type "%s"',
                $type
            ));
        }
        // for each arg
        //

    }

    /**
     * @return array{0:self::T_*,1:(string),2?:string}
     */
    private function parseArgument(string $arg): array
    {
        if (substr($arg, 0, 1) !== '-') {
            return [self::T_ARG, $arg];
        }

        // long option
        if (substr($arg, 1, 1) === '-') {
            $equalPos = strpos($arg, '=');
            if ($equalPos !== false) {
                // option with value
                return [
                    self::T_OPT,
                    substr($arg, strpos($arg, '=') + 1),
                    substr($arg, 2, $equalPos - 2),
                ];
            }

            // boolean flag
            return [
                self::T_OPT_FLAG,
                substr($arg, 2)
            ];
        }

        // short option
        $optionName = substr($arg, 1, 1);
        $optionValueString = substr($arg, 2) ?: null;
        if ($optionValueString == null) {
            return [self::T_OPT_SHORT_FLAG, '', $optionName];
        }

        return [
            self::T_OPT_SHORT,
            $optionValueString,
            $optionName,
        ];
    }

    /**
     * @param ArgumentDefinition[] $argumentDefinitions
     * @param list<string> $args
     */
    private function mapArgument(
        CommandDefinition $commandDefinition,
        object $target,
        array &$args,
        array &$argumentDefinitions,
        string $arg
    ): bool
    {
        $argumentDefinition = array_shift($argumentDefinitions);

        if ($argumentDefinition instanceof ArgumentDefinition) {
            if ($argumentDefinition->type instanceof ListType) {
                $target->{$argumentDefinition->name} = [$arg, ...$args];
                $args = [];
                return true;
            }
            $target->{$argumentDefinition->name} = $argumentDefinition->type->parse($arg);
            return false;
        }

        $subCommandDefinition = $commandDefinition->getCommand($arg);
        if (null !== $subCommandDefinition) {
            $this->parse($subCommandDefinition, $args);
            return true;
        }
        throw new ParseError(sprintf(
            'Extra argument with value "%s" provided for command <%s>',
            $arg,
            $commandDefinition->name
        ));
    }

    private function mapOption(
        CommandDefinition $commandDefinition,
        object $target,
        string $name,
        string $value
    ): void
    {
        $option = $commandDefinition->getOption($name);
        $target->{$option->name} = $option->type->parse($value);
    }

    private function mapShortOption(CommandDefinition $commandDefinition, object $target, string $name, string $value)
    {
        $option = $commandDefinition->getShortOption($name);
        $target->{$option->name} = $option->type->parse($value);
    }

    private function mapFlag(CommandDefinition $commandDefinition, object $target, string $name): void
    {
        $option = $commandDefinition->getOption($name);
        $target->{$option->name} = true;
    }
}
