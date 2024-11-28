<?php

declare(strict_types=1);

namespace Doc\Documentation;

class PhpTypes
{
    public static function isPrimitive(string $type): bool
    {
        return in_array($type, [
            'int',
            'integer',
            'long',
            'float',
            'double',
            'string',
            'byte',
            'binary',
            'boolean',
            'date',
            'dateTime',
            'password',
            'number',
            'object',
            'array',
            'iterable',
            'callable',
            'void'
        ]);
    }

    public static function isInternalType(string $type): bool
    {
        if(self::isPrimitive($type)) {
            return true;
        }

        return in_array($type, [
            'DateTime',
            'DateTimeImmutable',
            'Carbon\Carbon',
            'Carbon\CarbonImmutable',
        ]);
    }
}