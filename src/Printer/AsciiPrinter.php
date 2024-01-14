<?php

namespace PhpTui\CliParser\Printer;

use PhpTui\CliParser\Metadata\Command;
use PhpTui\CliParser\Metadata\Option;
use RuntimeException;

class AsciiPrinter
{
    public function print(object $object): string
    {
        if ($object instanceof Command) {
            return $this->printCommad($object);
        }

        throw new RuntimeException(sprintf(
            'Do not know how to print "%s"',
            $object::class
        ));
    }

    private function printCommad(Command $object, int $level = 0): string
    {
        $out = [];

        if ($object->help) {
            $out[] = $object->help;
            $out[] = '';
        }

        $out[] = $this->commandSynopsis($object, $level);
        $out[] = '';

        if (count($object->options)) {
            $out[] = 'Options:';
            $out[] = '';
            foreach ($object->options as $option) {
                $out[] = $this->optionSynopsis($option, $level+1);
            }
            $out[] = '';
        }

        if (count($object->commands())) {
            $out[] = 'Commands:';
            $out[] = '';
            foreach ($object->commands() as $command) {
                $out[] = $this->commandSynopsis($command, $level+1);
            }
        }

        return implode("\n" ,$out);
    }

    private function commandSynopsis(Command $command, int $level): string
    {
        $out = [];
        $out[] = $command->name;
        foreach ($command->arguments() as $argument) {
            $out[] = sprintf('<%s>', $argument->name);
        }
        foreach ($command->options as $option) {
            $out[] = sprintf('[--%s%s]', $option->parseName, $option->short ? sprintf('|-%s', $option->short) : '');
        }

        foreach ($command->commands() as $command) {

        }

        return $this->indent(implode(' ', $out), $level);
    }

    private function indent(string $string, int $level): string
    {
        return str_repeat('  ', $level) . $string;
    }

    private function optionSynopsis(Option $option, int $level): string
    {
        $out = [];
        if ($option->short) {
            $out[] = sprintf("-%s, --%s\t", $option->short, $option->parseName);
        } else {
            $out[] = sprintf("--%s\t", $option->parseName);
        }
        if ($option->help) {
            $out[] = $option->help;
        }
        $out[] = sprintf('(%s)', $option->type->toString());

        return $this->indent(implode(" ", $out), $level);
    }
}
