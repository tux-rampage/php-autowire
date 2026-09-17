<?php
declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use TuxRampage\Autowire\Injectable;

interface Constructable
{
    /**
     * Returns the name that can be constructed with the new operator
     */
    public function toConstructableName(): string;

    public function getParameters(): ParameterList;

    /**
     * Build constructor parameters for a constructable item
     *
     * @param array<string, Injectable> $values
     * @return array<string, Injectable>|list<Injectable>
     */
    public function buildConstructorParameters(array $values): array;
}
