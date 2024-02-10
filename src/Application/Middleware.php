<?php

namespace PhpTui\CliParser\Application;

interface Middleware
{
    public function handle(Handler $handler, Context $context): int;
}
