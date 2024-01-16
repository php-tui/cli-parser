<?php

namespace PhpTui\CliParser\Type;

use PhpTui\CliParser\Error\ParseError;


/**
 * @implements Type<bool>
 */
final class BooleanType implements Type
{
    public function toString(): string
    {
        return 'boolean';
    }

    public function parse(string $value): mixed
    {
        $trueValues = ['true', 'yes', 'on'];
        $falseValues = ['false', 'no', 'off'];
        if (in_array($value, $trueValues)) {
            return true;
        }
        if (in_array($value, $falseValues)) {
            return false;
        }

        throw new ParseError(sprintf(
            'Boolean value must be one of: %s, got "%s"',
            implode(', ', array_merge($trueValues, $falseValues)),
            $value
        ));
    }
}
