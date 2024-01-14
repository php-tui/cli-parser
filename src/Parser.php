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

        $cmd = $this->loader->load($target);

        foreach ($cmd->arguments as $param) {
            $target->{$param->name} = array_shift($args);
        }
    }
}
