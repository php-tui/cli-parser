<?php

namespace PhpTui\CliParser\Application\Middleware;

use PhpTui\CliParser\Application\Context;
use PhpTui\CliParser\Application\Handler;
use PhpTui\CliParser\Application\Middleware;
use PhpTui\CliParser\Printer\AsciiPrinter;
use PhpTui\CliParser\Type\BooleanType;
use RuntimeException;

final class HelpMiddleware implements Middleware
{
    public function __construct(private AsciiPrinter $printer, private string $helpOption = 'help')
    {
    }

    /**
     * @param Context<object,object> $context
     */
    public function handle(Handler $handler, Context $context): int
    {
        $definition = $context->applicationDefinition();
        $option = $definition->options()->getOrNull($this->helpOption);

        if (null === $option) {
            throw new RuntimeException(sprintf(
                'In order to use the %s middleware you must add a `%s` property to your %s',
                HelpMiddleware::class,
                $this->helpOption,
                $context->application()::class
            ));
        }

        if (!$option->type instanceof BooleanType) {
            throw new RuntimeException(sprintf(
                '"help" property must be a boolean type in order to use the %s middleware',
                HelpMiddleware::class
            ));
        }

        $value = $context->application()->{$this->helpOption};
        if (!$value) {
            return $handler->handle($context);
        }

        echo $this->printer->print($context->commandDefinition());

        return 0;
    }
}
