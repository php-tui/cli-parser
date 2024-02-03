<?php

namespace PhpTui\CliParser\Application\CommandHandler;

use PhpTui\CliParser\Application\CommandHandler;

final class CallableHandler implements CommandHandler
{
    public function handle(object $command): ?int
    {
        return 0;
    }
}
