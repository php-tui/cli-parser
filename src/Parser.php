<?php

namespace PhpTui\CliParser;

use PhpTui\CliParser\Error\ParseError;
use PhpTui\CliParser\Metadata\Command;
use PhpTui\CliParser\Type\BooleanType;
use PhpTui\CliParser\Type\ListType;
use RuntimeException;

final class Parser
{
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
        if (empty($args)) {
            throw new RuntimeException(
                'Command line arguments cannot be empty'
            );
        }

        $script = array_shift($args);

        [$arguments, $optionsToParse] = $this->parseArguments($args);

        $cmd = $this->loader->load($target);

        $firstOptional = null;
        $firstList = null;
        $listItems = [];
        foreach ($cmd->arguments() as $param) {
            if ($param->required && $firstOptional) {
                throw new ParseError(sprintf(
                    'Required argument <%s> cannot be positioned after optional argument <%s>',
                    $param->name,
                    $firstOptional->name
                ));
            }

            if ($firstList !== null) {
                throw new ParseError(sprintf(
                    'No arguments area allowed after list argument <%s>',
                    $firstList->name
                ));
            }

            if ($param->type instanceof ListType) {
                $target->{$param->name} = $arguments;
                $arguments = [];
                $firstList = $param;
                continue;
            }

            $argString = array_shift($arguments);

            if (null === $argString) {
                if ($param->required === true) {
                    throw new ParseError(sprintf(
                        'Not enough arguments, need "%s"',
                        count($cmd->arguments())
                    ));
                }

                continue;
            }

            $target->{$param->name} = $param->type->parse($argString);
            unset($args[array_search($param->name, $args)]);
            if ($param->required === false) {
                $firstOptional = $param;
            }
        }

        $cliOptions = $cmd->optionsKeyedByName();

        foreach ($optionsToParse as $name => $optionValue) {
            if (!isset($cliOptions[$name])) {
                continue;
            }

            $option = $cliOptions[$name];
            if ($optionValue === null && !$option->type instanceof BooleanType) {
                throw new ParseError(sprintf('Option "%s" of type "%s" must have a value', $option->parseName, $option->type->toString()));
            }
            unset($args[array_search($option->name, $args)]);
            $target->{$option->name} = $option->type->parse($optionValue ?? 'true');
        }

        $nextCommandName = array_shift($arguments);

        if (null === $nextCommandName) {
            return;
        }

        $nextCommand = $cmd->getCommand($nextCommandName);
        if ($nextCommand) {
            $this->parse($target->{$nextCommand->name}, $args);
            return;
        }

        throw new ParseError(sprintf('Superflous argument with value "%s" provided', $nextCommandName));
    }

    /**
     * @return array{list<string>,array<string,?string>}
     * @param list<string> $args
     */
    private function parseArguments(array $args): array
    {
        $arguments = [];
        $optionsToParse = [];
        $shortOptions = [];
        
        foreach ($args as $arg) {
            if (substr($arg, 0, 1) !== '-') {
                $arguments[] = $arg;
                continue;
            }
        
            if (substr($arg, 0, 1) === '-') {
                $nameOffset = 1;
                if (substr($arg, 1, 1) === '-') {
                    // long option
                    $equalPos = strpos($arg, '=');
                    if ($equalPos !== false) {
                        // option with value
                        $optionName = substr($arg, 2, $equalPos - 2);
                        $optionValueString = substr($arg, strpos($arg, '=') + 1);
                    } else {
                        // boolean flag
                        $optionName = substr($arg, 2);
                        $optionValueString = null;
                    }
                } else {
                    // short option
                    $optionName = substr($arg, 1, 1);
                    $optionValueString = substr($arg, 2);
                }
                $optionsToParse[$optionName] = $optionValueString;
                continue;
            }
        
            $shortOptions[substr($arg, 1)] = $arg;
        }
        return [$arguments, $optionsToParse];
    }
}
