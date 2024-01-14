<?php

namespace PhpTui\CliParser;

use PhpTui\CliParser\Attribute\Arg;
use PhpTui\CliParser\Attribute\Cmd;
use PhpTui\CliParser\Attribute\Opt;
use PhpTui\CliParser\Metadata\Argument;
use PhpTui\CliParser\Metadata\Command;
use PhpTui\CliParser\Metadata\Option;
use PhpTui\CliParser\Type\TypeFactory;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

final class Loader
{
    const ROOT_NAME = '__ROOT__';

    public function load(object $object): Command
    {
        return $this->loadCommand(null, null, $object);
    }

    private function loadCommand(?ReflectionProperty $parentProperty, ?Cmd $attr, object $object): Command
    {
        $reflection = new ReflectionClass($object);

        $args = [];
        $options = [];

        foreach ($reflection->getProperties() as $property) {
            foreach ($property->getAttributes(Arg::class) as $arg) {
                $args[] = $this->loadArg($property, $arg);
                continue 2;
            }
            foreach ($property->getAttributes(Opt::class) as $opt) {
                $options[] = $this->loadOption($property, $opt);
                continue 2;
            }
            foreach ($property->getAttributes(Cmd::class) as $opt) {
                $args[] = $this->loadCommand($property, $opt->newInstance(), $object->{$property->getName()});
                continue 2;
            }
        }

        return new Command(
            name: $parentProperty?->getName() ?? self::ROOT_NAME,
            arguments: $args,
            options: $options,
            help: $attr?->help,
        );
    }

    /**
     * @param ReflectionAttribute<Arg> $arg
     */
    private function loadArg(ReflectionProperty $property, ReflectionAttribute $arg): Argument
    {
        $attribute = $arg->newInstance();
        $name = $property->getName();
        $type = TypeFactory::fromReflectionType($property->getType());

        return new Argument(
            name: $name,
            type: $type,
            help: $attribute->help,
        );
    }

    /**
     * @param ReflectionAttribute<Opt> $opt
     */
    private function loadOption(ReflectionProperty $property, ReflectionAttribute $opt): Option
    {
        $attribute = $opt->newInstance();
        $parseName = self::resolveName($property->getName(), $attribute);
        $type = TypeFactory::fromReflectionType($property->getType());
        return new Option(
            name: $property->getName(),
            short: $this->parseShortName($attribute->short),
            type: $type,
            parseName: $parseName,
            help: $attribute->help,
        );
    }

    private static function resolveName(string $string, Opt|Arg $opt): string
    {
        if ($opt->name !== null) {
            return $opt->name;
        }

        return $string;
    }

    private function parseShortName(?string $name): ?string
    {
        if (null === $name) {
            return null;
        }

        if (strlen($name) !== 1) {
            throw new RuntimeException(sprintf(
                'Short name must be 1 character long, got "%s"',
                $name
            ));
        }

        return $name;
    }
}
