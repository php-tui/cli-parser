<?php

namespace PhpTui\CliParser;

use PhpTui\CliParser\Attribute\Arg;
use PhpTui\CliParser\Attribute\Cmd;
use PhpTui\CliParser\Attribute\Opt;
use PhpTui\CliParser\Error\ParseError;
use PhpTui\CliParser\Metadata\ArgumentDefinition;
use PhpTui\CliParser\Metadata\CommandDefinition;
use PhpTui\CliParser\Metadata\OptionDefinition;
use PhpTui\CliParser\Type\TypeFactory;
use ReflectionAttribute;
use ReflectionObject;
use ReflectionProperty;
use RuntimeException;

final class Loader
{
    const ROOT_NAME = '__ROOT__';

    public function load(object $object): CommandDefinition
    {
        $cmd = $this->loadCommand($object, null);
        $this->validate($cmd);
        return $cmd;
    }

    private function loadCommand(object $object, ?ReflectionProperty $parent): CommandDefinition
    {
        $reflection = new ReflectionObject($object);

        $args = [];
        $options = [];
        $name = $parent?->getName();
        $help = null;

        foreach ($reflection->getAttributes(Cmd::class) as $attribute) {
            $cmd = $attribute->newInstance();
            $name = $cmd->name ?? $name;
            $help = $cmd->help;
        }

        foreach ($reflection->getProperties() as $property) {
            foreach ($property->getAttributes(Arg::class) as $arg) {
                $args[] = $this->loadArg($property, $arg);
                continue 2;
            }
            foreach ($property->getAttributes(Opt::class) as $opt) {
                $options[] = $this->loadOption($property, $opt);
                continue 2;
            }
            $subCmd = $property->getValue($object);
            if (is_object($subCmd)) {
                $args[] = $this->loadCommand($subCmd, $property);
            }
        }

        return new CommandDefinition(
            name: $name ?? self::ROOT_NAME,
            propertyName: $parent?->getName(),
            arguments: $args,
            options: $options,
            help: $help,
        );
    }

    /**
     * @param ReflectionAttribute<Arg> $arg
     */
    private function loadArg(ReflectionProperty $property, ReflectionAttribute $arg): ArgumentDefinition
    {
        $attribute = $arg->newInstance();
        $name = $property->getName();
        $type = TypeFactory::fromReflectionType($property->getType());

        return new ArgumentDefinition(
            name: $name,
            type: $type,
            help: $attribute->help,
            required: $attribute->required,
        );
    }

    /**
     * @param ReflectionAttribute<Opt> $opt
     */
    private function loadOption(ReflectionProperty $property, ReflectionAttribute $opt): OptionDefinition
    {
        $attribute = $opt->newInstance();
        $parseName = self::resolveName($property->getName(), $attribute);
        $type = TypeFactory::fromReflectionType($property->getType());
        return new OptionDefinition(
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

    private function validate(CommandDefinition $cmd): void
    {
        $firstOptional = null;
        foreach ($cmd->arguments() as $argument) {
            if ($firstOptional && $argument->required) {
                throw new ParseError(sprintf(
                    'Required argument <%s> cannot be positioned after optional argument <%s>',
                    $argument->name,
                    $firstOptional->name,
                ));
            }
            if ($argument->required === false) {
                $firstOptional = $argument;
            }

        }
    }
}
