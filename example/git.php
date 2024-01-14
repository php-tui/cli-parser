<?php

use PhpTui\Term\Attribute\Arg;
use PhpTui\Term\Attribute\Opt;

class GitArgs {

    #[Arg(help: 'Clone a repository into a new directory')]
    public GitCloneArgs $clone;

    #[Arg(help: 'Create an empty git repository')]
    public GitInitArgs $init;

    #[Opt(short: 'v', long: 'version', help: 'Create an empty git repository')]
    public bool $version;

    #[Opt(help: 'Show help')]
    public bool $help;

    #[Opt(short: 'C', help: 'Run git as ig it were executed in this path')]
    public bool $cwd;
}

class GitCloneArgs {
    #[Arg(help: 'Repository to clone')]
    public string $repo;

    #[Opt(name: 'recurse-submodules', help: 'Initialize submodules in the clone')]
    public string $recurseSubModules;
}

class GitInitArgs {
}
