<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection\Type;

final readonly class IntersectionType
{
    /**
     * @var non-empty-list<ClassName>
     */
    public array $types;

    public function __construct(ClassName $type, ClassName ...$types)
    {
        $this->types = [$type, ...array_values($types)];
    }
}
