<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection\Type;

use InvalidArgumentException;

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

    /**
     * @psalm-assert 'int'|'string'|'bool'|'float'|'null'|'array'|'callable'|'mixed'|'iterable'|'object'|'resource'|'true'|'false' $type
     */
    public static function fromString(string $type): self
    {
        if (!in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException("Type '$type' is not a valid builtin type.");
        }

        return new self($type);
    }
}
