<?php

namespace PhpTui\CliParser\Application;

use PhpTui\CliParser\Loader;
use PhpTui\CliParser\Parser;
use RuntimeException;

final class Application
{
    public function __construct(
        private object $cli,
        private Loader $loader,
        private Parser $parser,
        private CommandHandler $handler,
    ) {
    }

    /**
     * @param list<string> $argv
     */
    public function run(array $argv): int
    {
        $definition = $this->loader->load($this->cli);
        array_shift($argv);
        $command = $this->parser->parse($definition, $this->cli, $argv);
        return $this->handler->handle($command);
    }
}
