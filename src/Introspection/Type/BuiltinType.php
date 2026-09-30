<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection\Type;

/**
 * @api
 */
final readonly class BuiltinType
{
    /**
     * @var list<string>
     */
    public const array TYPES = [
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
