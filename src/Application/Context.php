<?php

namespace PhpTui\CliParser\Application;

use PhpTui\CliParser\Metadata\AbstractCommandDefinition;
use PhpTui\CliParser\Metadata\ApplicationDefinition;

/**
 * @template-covariant TApplication of object
 * @template-covariant TCommand of object
 */
final class Context
{
    public function __construct(
        private ApplicationDefinition $applicationDefinition,
        private AbstractCommandDefinition $commandDefinition,
        /** @var TApplication */
        private object $application,
        /** @var TCommand */
        private object $command
    ) {
    }

    public function applicationDefinition(): ApplicationDefinition
    {
        return $this->applicationDefinition;
    }

    public function commandDefinition(): AbstractCommandDefinition
    {
        return $this->commandDefinition;
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
