<?php

namespace PhpTui\CliParser\Printer;

use PhpTui\CliParser\Metadata\AbstractCommandDefinition;
use PhpTui\CliParser\Metadata\ApplicationDefinition;
use PhpTui\CliParser\Metadata\OptionDefinition;
use RuntimeException;

final class AsciiPrinter
{
    public function print(object $object): string
    {
        if ($object instanceof AbstractCommandDefinition) {
            return $this->printCommad($object);
        }

        throw new RuntimeException(sprintf(
            'Do not know how to print "%s"',
            $object::class
        ));
    }

    private function printCommad(AbstractCommandDefinition $object, int $level = 0): string
    {
        $out = [];

        if ($object->help) {
            $out[] = $object->help;
            $out[] = '';
        }

        $out[] = $this->commandSynopsis($object, $level);
        $out[] = '';

        if (count($object->options())) {
            $out[] = 'Options:';
            $out[] = '';
            foreach ($object->options() as $option) {
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

        return implode("\n", $out);
    }

    private function commandSynopsis(AbstractCommandDefinition $command, int $level): string
    {
        $out = [];
        $title = [];
        $title[] = $command->name;
        if ($command instanceof ApplicationDefinition) {
            if ($command->version !== null) {
                $title[] = $command->version;
            }
            if ($command->author !== null) {
                $title[] = 'by';
                $title[] = $command->author;
            }
        }
        $out[] = implode(' ', $title);
        foreach ($command->arguments() as $argument) {
            $out[] = sprintf('<%s>', $argument->name);
        }
        $options = [];
        foreach ($command->options() as $option) {
            $options[] = sprintf('[--%s%s]', $option->parseName, $option->short ? sprintf('|-%s', $option->short) : '');
        }

        $out[] = implode(' ', $options);
        $out[] = $command->help;

        return $this->indent(implode("\t", $out), $level);
    }

    private function indent(string $string, int $level): string
    {
        return str_repeat('  ', $level) . $string;
    }

    private function optionSynopsis(OptionDefinition $option, int $level): string
    {
        $out = [];
        if ($option->short) {
            $out[] = sprintf("%2s, --%s\t", '-'.$option->short, $option->parseName);
        } else {
            $out[] = sprintf("    --%s\t", $option->parseName);
        }
        if ($option->help) {
            $out[] = $option->help;
        }
        $out[] = sprintf('(%s)', $option->type->toString());

        return $this->indent(implode(' ', $out), $level);
    }
}
