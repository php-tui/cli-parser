<?php

namespace PhpTui\CliParser\Metadata;

use PhpTui\CliParser\Type\Type;

final class Option
{
    public readonly string $parseName;

    /**
     * @template TParseType
     * @param Type<TParseType> $type
     */
    public function __construct(
        public readonly string $name,
        public readonly Type $type,
        ?string $parseName = null,
        public readonly ?string $short = null,
        public readonly ?string $help = null
    ) {
        $this->parseName = $parseName ?: $name;
    }

}
