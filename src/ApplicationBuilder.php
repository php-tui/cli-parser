<?php

namespace PhpTui\CliParser;

use PhpTui\CliParser\Application\Application;
use PhpTui\CliParser\Application\CommandHandler;
use PhpTui\CliParser\Application\Context;
use PhpTui\CliParser\Metadata\Loader;
use PhpTui\CliParser\Parser\Parser;

/**
 * @template TApplication of object
 * @template-covariant TCommand of object
 */
final class ApplicationBuilder
{
    /**
     * @var array<class-string<TCommand>,callable(Context<TApplication,TCommand>):int>
     */
    private array $handlers = [];

    /**
     * @param TApplication $cli
     */
    public function __construct(
        private object $cli
    ) {
    }

    /**
     * @template TCli of object
     * @param TCli $cli
     * @return self<TCli,object>
     */
    public static function fromSpecification(object $cli): self
    {
        return new self($cli);
    }
    /**
     * @template TCmd of object
     * @param class-string<TCmd> $cmdFqn
     * @param callable(Context<TApplication,TCmd>):int $handler
     * @return self<TApplication,object>
     */
    public function addHandler(string $cmdFqn, callable $handler): self
    {
        /** @phpstan-ignore-next-line */
        $this->handlers[$cmdFqn] = $handler;
        return $this;
    }

    public function build(): Application
    {
        return new Application(
            $this->cli,
            new Loader(),
            new Parser(),
            new CommandHandler($this->handlers)
        );
    }
}
