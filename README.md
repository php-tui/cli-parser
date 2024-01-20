CLI Parser
==========

Type safe CLI parser for PHP inspired by [Kong](https://github.com/alecthomas/kong).

Usage
-----

Taking the example directly from Kong:

```
shell rm [-f] [-r] <paths> ...
shell ls [<paths> ...]
```

Can be represented as:

```php
<?php

$cli = new #[Cmd('My App', help: 'Application to list and remove files')] class(
    new #[Cmd('rm', help: 'Remove files')] class {
        #[Opt(help: 'Force removal')]
        public bool $force = false;

        #[Opt(help: 'Recursively remove files', short: 'r')]
        public bool $recursive = false;

        /** @var list<string> */
        #[Opt(help: 'Paths to remove', name: 'path', type: 'path')]
        public array $paths = [];
    },
    new #[Cmd('ls', help: 'List files')] class {
        /** @var list<string> */
        #[Opt(help: 'Paths to list', name: 'path', type: 'path')]
        public array $paths = [];
    },
) {
    public function __construct(
        public object $rmCmd,
        public object $lsCmd,
    ) {}
};
```

We can then parse any CLI arguments with:

```
(new Parser())->parse($cli, $argv);
```



Parsing
-------

- [x] `<arg1>` single argument
- [x] `<arg1> <arg2>` multiple arguments
- [x] `<arg1> ...` repeat arguments
- [x] `--option` Option flag
- [x] `--option=foo` Option with value
- [x] `-o` Short option flag
- [x] `-ofoo` Short option with value
- [ ] branching arguments

