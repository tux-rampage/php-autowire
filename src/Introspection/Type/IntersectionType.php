<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection\Type;

final readonly class IntersectionType
{
    /**
     * @param non-empty-list<ClassName> $types
     */
    public function __construct(public array $types)
    {
    }
}
