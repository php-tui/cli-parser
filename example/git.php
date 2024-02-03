<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpTui\CliParser\ApplicationBuilder;
use PhpTui\CliParser\Attribute\App;
use PhpTui\CliParser\Attribute\Cmd;
use PhpTui\CliParser\Attribute\Opt;
use PhpTui\CliParser\Attribute\Arg;

#[App(name: 'Git', version: '1.0', author: 'Daniel Leech')]
final class GitCmd
{
    public CloneCmd $clone;

    public InitCmd $init;

    #[Opt(short: 'v', long: 'version', help: 'Create an empty git repository')]
    public bool $version = false;

    #[Opt(help: 'Show help')]
    public bool $help = false;

    #[Opt(short: 'C', help: 'Run git as ig it were executed in this path')]
    public string $cwd;
}

#[Cmd(help: 'Clone a repository into a new directory')]
final class CloneCmd
{
    #[Arg(help: 'Repository to clone', required: true)]
    public string $repo;

    #[Opt(name: 'recurse-submodules', short: 'R', help: 'Initialize submodules in the clone')]
    public bool $recurseSubModules = false;
}

#[Cmd(help: 'Create an empty git repository')]
final class InitCmd
{
    #[Arg(help: 'Repository to clone')]
    public string $repo;

    #[Opt(name: 'recurse-submodules', help: 'Initialize submodules in the clone')]
    public bool $recurseSubModules;
}

$cli = new GitCmd();
$cli->init = new InitCmd();
$cli->clone = new CloneCmd();

$application = ApplicationBuilder::fromSpecification($cli)
    ->addHandler(InitCmd::class, function (InitCmd $cmd) {
        dump($cmd);
    })
    ->addHandler(CloneCmd::class, function (CloneCmd $cmd) {
        println(sprintf('Git cloning %s...', $cmd->repo));
        if ($cmd->recurseSubModules) {
            println('... and recursing submodules');
        }
    })
    ->build();
;
$application->run($argv);

function println(string $message): void
{
    echo $message ."\n";
}
