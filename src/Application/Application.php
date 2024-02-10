<?php

namespace PhpTui\CliParser\Application;

use PhpTui\CliParser\Metadata\Loader;
use PhpTui\CliParser\Parser\Parser;
use Throwable;

final class Application
{
    public function __construct(
        private object $cli,
        private Loader $loader,
        private Parser $parser,
        private Handler $handler,
        private ExceptionHandler $exceptionHandler,
    ) {
    }

    /**
     * @param list<string> $argv
     */
    public function run(array $argv): int
    {
        $applicationDefinition = $this->loader->load($this->cli);

        array_shift($argv);

        try {
            [$commandDefinition, $command] = $this->parser->parse(
                $applicationDefinition,
                $this->cli,
                $argv
            );
        } catch (Throwable $exception) {
            return $this->exceptionHandler->handle(
                new ExceptionContext($applicationDefinition, $exception)
            );
        }

        return $this->handler->handle(new Context(
            $applicationDefinition,
            $commandDefinition,
            $this->cli,
            $command
        ));
    }
}
