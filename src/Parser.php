<?php

namespace PhpTui\CliParser;

use PhpTui\CliParser\Error\ParseError;
use PhpTui\CliParser\Type\BooleanType;
use ReflectionClass;
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

        $cmd = $this->loader->load($target);

        foreach ($cmd->arguments() as $param) {
            $argString = array_shift($arguments);
            if (null === $argString) {
                throw new ParseError(sprintf('Not enough arguments, need "%s"',
                    count($cmd->arguments())
                ));
            }
            $target->{$param->name} = $param->type->parse($argString);
        }

        $cliOptions = $cmd->optionsKeyedByName();

        foreach ($optionsToParse as $name => $optionValue) {
            if (!isset($cliOptions[$name])) {
                throw new RuntimeException(sprintf(
                    'Unknown CLI option "%s", known options: "%s"',
                    $name,
                    implode('", "', array_keys($cliOptions)),
                ));
            }

            $option = $cliOptions[$name];
            if ($optionValue === null && !$option->type instanceof BooleanType) {
                throw new ParseError(sprintf('Option "%s" of type "%s" must have a value', $option->parseName, $option->type->toString()));
            }
            $target->{$option->name} = $option->type->parse($optionValue ?? 'true');
        }
    }
}
