<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpTui\CliParser\Attribute\Cmd;
use PhpTui\CliParser\Attribute\Opt;
use PhpTui\CliParser\Attribute\Arg;
use PhpTui\CliParser\Loader;
use PhpTui\CliParser\Parser;
use PhpTui\CliParser\Printer\AsciiPrinter;

class GitArgs {

    #[Cmd(help: 'Clone a repository into a new directory')]
    public GitCloneArgs $clone;

    #[Cmd(help: 'Create an empty git repository')]
    public GitInitArgs $init;

    #[Opt(short: 'v', long: 'version', help: 'Create an empty git repository')]
    public bool $version;

    #[Opt(help: 'Show help')]
    public bool $help;

    #[Opt(short: 'C', help: 'Run git as ig it were executed in this path')]
    public string $cwd;
}

class GitCloneArgs {
    #[Arg(help: 'Repository to clone')]
    public string $repo;

    #[Opt(name: 'recurse-submodules', short: 'R', help: 'Initialize submodules in the clone')]
    public string $recurseSubModules;
}

class GitInitArgs {
    #[Arg(help: 'Repository to clone')]
    public string $repo;

    #[Opt(name: 'recurse-submodules', help: 'Initialize submodules in the clone')]
    public string $recurseSubModules;
}

$cli = new GitArgs();
$cli->init = new GitInitArgs();
$cli->clone = new GitCloneArgs();

echo (new AsciiPrinter())->print((new Loader())->load($cli));
