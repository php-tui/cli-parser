<?php

namespace PhpTui\CliParser;

use PhpTui\CliParser\Application\Application;
use PhpTui\CliParser\Application\CommandHandler;

final class ApplicationBuilder
{
    /**
     * @var array<string,callable(object):int>
     */
    private array $handlers = [];

    public function __construct(private object $cli)
    {
    }

    public static function fromSpecification(object $cli): self
    {
        return new self($cli);
    }
    /**
     * @param callable(object):int $handler
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
