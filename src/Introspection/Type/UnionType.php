<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection\Type;

final readonly class UnionType
{
    /**
     * @param non-empty-list<BuiltinType|ClassName|IntersectionType> $types
     */
    public function __construct(
        public array $types
    ) {
    }
}
