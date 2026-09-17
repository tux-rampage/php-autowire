<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

interface IntrospectionStrategy
{
    public function hasType(string $type): bool;
    public function introspect(string $type): TypeDefinition;
}
