<?php

namespace PhpTui\CliParser\Parser;

use PhpTui\CliParser\Error\ParseErrorWithContext;
use PhpTui\CliParser\Metadata\AbstractCommandDefinition;
use PhpTui\CliParser\Metadata\ApplicationDefinition;
use PhpTui\CliParser\Metadata\ArgumentDefinition;
use PhpTui\CliParser\Error\ParseError;
use PhpTui\CliParser\Type\ListType;
use RuntimeException;

final class Parser
{
    private const T_ARG = 'T_ARG';
    private const T_OPT = 'T_OPT';
    private const T_OPT_FLAG = 'T_FLAG';
    private const T_OPT_SHORT = 'T_OPT_SHORT';
    private const T_OPT_SHORT_FLAG = 'T_OPT_SHORT_FLAG';

    /**
     * Parse the CLI arguments into the given CLI object and return the
     * resolved command object.
     *
     * @param string[] $args
     * @return array{AbstractCommandDefinition,object}
     */
    public function parse(ApplicationDefinition $definition, object $target, array $args): array
    {
        $cmd = $this->parseCommand($target, $definition, $args);
        return $cmd;
    }

    /**
     * @param list<string> $args
     * @return array{AbstractCommandDefinition,object}
     */
    public function parseCommand(object $target, AbstractCommandDefinition $commandDefinition, array $args): array
    {
        try {
            return $this->doParseCommand($target, $commandDefinition, $args);
        } catch (ParseError $notFound) {
            throw new ParseErrorWithContext($notFound->getMessage(), $commandDefinition, $notFound);
        }
    }

    /**
     * @param list<string> $args
     * @return array{AbstractCommandDefinition,object}
     */
    public function doParseCommand(object $target, AbstractCommandDefinition $commandDefinition, array $args): array
    {
        $argumentDefinitions = $commandDefinition->arguments()->toArray();
        $commandDefinitions = $commandDefinition->commands();

        $longOptions = [];

        while ($arg = array_shift($args)) {
            $parsed = $this->parseArgument($arg);

            $type = $parsed[0];
            $value = $parsed[1];
            $name = $parsed[2] ?? null;

            if ($type === self::T_ARG) {
                [$newCommandDefinition, $newTarget] = $this->mapArgument(
                    $commandDefinition,
                    $target,
                    $args,
                    $argumentDefinitions,
                    $arg
                );
                if ($newTarget !== $target) {
                    return [$newCommandDefinition, $newTarget];
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
            if ($type === self::T_OPT_SHORT_FLAG) {
                $this->mapShortOptionFlag($commandDefinition, $target, $name ?? '');
                continue;
            }

            /** @phpstan-ignore-next-line */
            throw new RuntimeException(sprintf(
                'Do not know how to map argument of type "%s"',
                $type
            ));
        }

        $requiredArguments = array_filter(
            $argumentDefinitions,
            fn (ArgumentDefinition $definition) => $definition->required,
        );
        if (count($requiredArguments)) {
            throw new ParseErrorWithContext(sprintf(
                'Missing required argument(s) <%s> in command "%s"',
                implode('>, <', array_map(fn (ArgumentDefinition $a) => $a->name, $requiredArguments)),
                $commandDefinition->name
            ), $commandDefinition);
        }

        return [$commandDefinition, $target];
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
     * @return array{AbstractCommandDefinition,object}
     */
    private function mapArgument(
        AbstractCommandDefinition $commandDefinition,
        object $target,
        array &$args,
        array &$argumentDefinitions,
        string $arg
    ): array {
        $argumentDefinition = array_shift($argumentDefinitions);

        if ($argumentDefinition instanceof ArgumentDefinition) {
            if ($argumentDefinition->type instanceof ListType) {
                $target->{$argumentDefinition->name} = array_map(
                    fn (string $arg) => $argumentDefinition->type->itemType()->parse($arg),
                    [$arg, ...$args]
                );
                $args = [];

                return [$commandDefinition, $target];
            }

            $target->{$argumentDefinition->name} = $argumentDefinition->type->parse($arg);

            return [$commandDefinition, $target];
        }

        $subCommandDefinition = $commandDefinition->commands()->getCommand($arg);
        if (null !== $subCommandDefinition) {
            $this->parseCommand(
                $target->{$subCommandDefinition->propertyName},
                $subCommandDefinition,
                $args
            );
            return [$subCommandDefinition, $target->{$subCommandDefinition->propertyName}];
        }
        throw new ParseErrorWithContext(sprintf(
            'Extra argument with value "%s" provided for command <%s>',
            $arg,
            $commandDefinition->name
        ), $commandDefinition);
    }

    private function mapOption(
        AbstractCommandDefinition $commandDefinition,
        object $target,
        string $name,
        string $value
    ): void {
        $option = $commandDefinition->options()->get($name);
        if ($option->type instanceof ListType) {
            $target->{$option->name} = array_map(
                fn (string $arg) => $option->type->itemType()->parse($arg),
                explode(',', $value)
            );
            return;
        }
        $target->{$option->name} = $option->type->parse($value);
    }

    private function mapShortOption(AbstractCommandDefinition $commandDefinition, object $target, string $name, string $value): void
    {
        $option = $commandDefinition->options()->shortOption($name);
        $target->{$option->name} = $option->type->parse($value);
    }

    private function mapFlag(AbstractCommandDefinition $commandDefinition, object $target, string $name): void
    {
        $option = $commandDefinition->options()->get($name);
        $target->{$option->name} = true;
    }

    private function mapShortOptionFlag(AbstractCommandDefinition $commandDefinition, object $target, string $name): void
    {
        $option = $commandDefinition->options()->shortOption($name);
        $target->{$option->name} = true;
    }
}
