<?php

namespace PhpTui\CliParser\Application;

use PhpTui\CliParser\Application\Exception\CommandHandlerNotFound;

final class CommandHandler
{
    /**
     * @param array<string,callable(object):int> $handlers
     */
    public function __construct(private array $handlers)
    {
    }

    public function handle(object $command): int
    {
        if (!isset($this->handlers[$command::class])) {
            throw new CommandHandlerNotFound($command::class);
        }

        return ($this->handlers[$command::class])($command);

    }
}
