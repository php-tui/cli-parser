<?php

namespace PhpTui\CliParser;

use PhpTui\CliParser\Application\Application;
use PhpTui\CliParser\Application\CommandHandler;
use PhpTui\CliParser\Application\Context;
use PhpTui\CliParser\Metadata\Loader;
use PhpTui\CliParser\Parser\Parser;

/**
 * @template-covariant TApplication of object
 * @template-covariant TCommand of object
 */
final class ApplicationBuilder
{
    /**
     * @var array<class-string,callable(Context<TApplication,TCommand>):int>
     */
    private array $handlers = [];

    public function __construct(private object $cli)
    {
    }

    /**
     * @return self<object,object>
     */
    public static function fromSpecification(object $cli): self
    {
        return new self($cli);
    }
    /**
     * @param class-string $cmdFqn
     * @param callable(Context<TApplication,TCommand>):int $handler
     * @return self<object,object>
     */
    public function addHandler(string $cmdFqn, callable $handler): self
    {
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
