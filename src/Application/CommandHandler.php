<?php

namespace PhpTui\CliParser\Application;

use PhpTui\CliParser\Application\Exception\CommandHandlerNotFound;

/**
 * @template TApplication of object
 * @template TCommand of object
 */
final class CommandHandler
{
    /**
     * @param array<class-string,callable(Context<TApplication,TCommand>):int> $handlers
     */
    public function __construct(private array $handlers)
    {
    }
    /**
     * @param Context<TApplication,TCommand> $context
     */
    public function handle(Context $context): int
    {
        if (!isset($this->handlers[$context->command()::class])) {
            throw new CommandHandlerNotFound($context->command()::class);
        }

        return ($this->handlers[$context->command()::class])($context);
    }
}
