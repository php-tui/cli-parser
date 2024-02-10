<?php

namespace PhpTui\CliParser\Error;

use PhpTui\CliParser\Metadata\AbstractCommandDefinition;
use RuntimeException;
use Throwable;

final class ParseErrorWithContext extends RuntimeException
{
    public function __construct(string $message, public readonly AbstractCommandDefinition $definition, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
