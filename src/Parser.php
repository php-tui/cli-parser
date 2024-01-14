<?php

namespace PhpTui\CliParser;

use ReflectionClass;
use RuntimeException;

final class Parser
{
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

        array_shift($args);

        $reflection = new ReflectionClass($target);

        foreach ($reflection->getProperties() as $property) {
        }
    }
}
