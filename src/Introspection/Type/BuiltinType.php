<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection\Type;

final readonly class BuiltinType
{
    public const TYPES = [
        'int',
        'string',
        'bool',
        'float',
        'null',
        'array',
        'callable',
        'mixed',
        'iterable',
        'object',
        'resource',
        'true',
        'false',
    ];

    /**
     * @param 'int'|'string'|'bool'|'float'|'null'|'array'|'callable'|'mixed'|'iterable'|'object'|'resource'|'true'|'false' $type
     */
    public function __construct(public string $type)
    {
    }
}
