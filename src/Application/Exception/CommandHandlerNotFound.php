<?php

namespace PhpTui\CliParser\Application\Exception;

use RuntimeException;

final class CommandHandlerNotFound extends RuntimeException
{
    public function __construct(string $cmdClass)
    {
        parent::__construct(sprintf(
            'Handler for command %s not found',
            $cmdClass
        ));
    }
}
