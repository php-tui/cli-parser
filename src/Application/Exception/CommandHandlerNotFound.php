<?php

namespace PhpTui\CliParser\Application\Exception;

use RuntimeException;

final class CommandHandlerNotFound extends RuntimeException
{
    public function __construct(string $command)
    {
        parent::__construct(sprintf(
            'Handler for command %s not found',
            $command::class
        ));
    }
}
