<?php
declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use TuxRampage\Autowire\Introspection\Type\ClassName;
use TuxRampage\Autowire\Introspection\Type\IntersectionType;
use TuxRampage\Autowire\Introspection\Type\UnionType;

interface TypeDefinition
{
    /**
     * The name of this type
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Return all supertypes of this type
     *
     * @return string[]
     */
    public function getSupertypes(): array;

    /**
     * Test whether this item satisfies the given type
     *
     * @param ClassName|IntersectionType|UnionType $type
     * @return bool
     */
    public function satisfies(ClassName|IntersectionType|UnionType $type): bool;
}
