<?php

namespace PhpTui\CliParser\Application;

interface Middleware
{
    /**
     * @param Context<object,object> $context
     */
    public function handle(Handler $handler, Context $context): int;
}
