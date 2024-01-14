<?php

namespace PhpTui\CliParser;

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

            if (substr($arg, 1, 1) === '-') {
                $optionsToParse[substr($arg, 2, strpos($arg, '=') - 2)] = $arg;
                continue;
            }

            $shortOptions[substr($arg, 1)] = $arg;
        }

        $cmd = $this->loader->load($target);

        foreach ($cmd->arguments as $param) {
            $target->{$param->name} = array_shift($arguments);
        }

        $cliOptions = $cmd->optionsKeyedByCliName();

        foreach ($optionsToParse as $name => $optionValue) {
            if (!isset($cliOptions[$name])) {
                throw new RuntimeException(sprintf(
                    'Unknown CLI option "%s", known options: "%s"',
                    $name,
                    array_keys($cliOptions)
                ));
            }
            $option = $cliOptions[$name];
            $target->{$option->name} = $option->type->parse($optionValue);
        }
    }
}
