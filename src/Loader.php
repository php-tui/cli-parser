<?php

namespace PhpTui\CliParser;

use PhpTui\CliParser\Attribute\Arg;
use PhpTui\CliParser\Metadata\Argument;
use PhpTui\CliParser\Metadata\Command;
use PhpTui\CliParser\Type\TypeFactory;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

final class Loader
{
    const ROOT_NAME = '__ROOT__';

    public function load(object $object): Command
    {
        $reflection = new ReflectionClass($object);

        $args = [];
        $options = [];

        foreach ($reflection->getProperties() as $property) {
            foreach ($property->getAttributes(Arg::class) as $arg) {
                $args[] = $this->loadArg($property, $arg);
                continue 2;
            }
        }

        return new Command(
            name: self::ROOT_NAME,
            arguments: $args,
            options: $options
        );
    }

    /**
     * @param ReflectionAttribute<Arg> $arg
     */
    private function loadArg(ReflectionProperty $property, ReflectionAttribute $arg): Argument
    {
        $name = $property->getName();
        $type = TypeFactory::fromReflectionType($property->getType());

        return new Argument(
            name: $name,
            type: $type,
        );
    }
}
