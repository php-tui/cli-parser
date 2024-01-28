<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpTui\CliParser\Attribute\App;
use PhpTui\CliParser\Attribute\Cmd;
use PhpTui\CliParser\Attribute\Opt;
use PhpTui\CliParser\Attribute\Arg;
use PhpTui\CliParser\Error\ParseError;
use PhpTui\CliParser\Loader;
use PhpTui\CliParser\Parser;
use PhpTui\CliParser\Printer\AsciiPrinter;

#[App(name: 'Git', version: '1.0', author: 'Daniel Leech')]
class GitCmd {

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
class CloneCmd {
    #[Arg(help: 'Repository to clone', required: true)]
    public string $repo;

    #[Opt(name: 'recurse-submodules', short: 'R', help: 'Initialize submodules in the clone')]
    public bool $recurseSubModules = false;
}

#[Cmd(help: 'Create an empty git repository')]
class InitCmd {
    #[Arg(help: 'Repository to clone')]
    public string $repo;

    #[Opt(name: 'recurse-submodules', help: 'Initialize submodules in the clone')]
    public bool $recurseSubModules;
}

$cli = new GitCmd();
$cli->init = new InitCmd();
$cli->clone = new CloneCmd();

$definition = (new Loader())->load($cli);

array_shift($argv);

try {
    $cmd = (new Parser())->parse($cli, $argv);
} catch (ParseError $e) {
    println($e->getMessage());
    exit(1);
}

if ($cli->help) {
    println((new AsciiPrinter())->print($definition));
    exit(0);
}
if ($cli->version) {
    println('1.0.0-pre');
    exit(0);
}
if ($cmd instanceof GitCmd) {
    println((new AsciiPrinter())->print($definition));
}
if ($cmd instanceof CloneCmd) {
    println(sprintf('Git cloning %s...', $cmd->repo));
    if ($cmd->recurseSubModules) {
        println(sprintf('... and recursing submodules', $cmd->repo));
    }
}
if ($cmd instanceof InitCmd) {
    dump($cmd);
}

function println(string $message): void
{
echo $message ."\n";
}
