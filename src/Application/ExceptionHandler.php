<?php

namespace PhpTui\CliParser\Application;

use PhpTui\CliParser\Printer\AsciiPrinter;

final class ExceptionHandler
{
    public function __construct(private AsciiPrinter $printer)
    {
    }

    public function handle(ExceptionContext $exceptionContext): int
    {
        echo $this->printer->print($exceptionContext);
        return 127;
    }
}
