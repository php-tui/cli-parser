<?php

namespace PhpTui\CliParser\Application;

use PhpTui\CliParser\Metadata\ApplicationDefinition;

/**
 * @template-covariant TApplication of object
 * @template-covariant TCommand of object
 */
final class Context
{
    public function __construct(
        private ApplicationDefinition $definition,
        /** @var TApplication */
        private object $application,
        /** @var TCommand */
        private object $command
    ) {
    }

    public function definition(): ApplicationDefinition
    {
        return $this->definition;
    }

    /**
     * @return TApplication
     */
    public function application(): object
    {
        return $this->application;
    }

    /**
     * @return TCommand
     */
    public function command(): object
    {
        return $this->command;
    }
}
