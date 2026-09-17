<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use function array_values;

final readonly class ConstructableClass extends ClassDefinition implements Constructable
{
    public function toConstructableName(): string
    {
        return $this->name;
    }

    public function getParameters(): ParameterList
    {
        return $this->parameters;
    }

    /**
     * @inheritDoc
     */
    public function buildConstructorParameters(array $values): array
    {
        return $this->parameters->buildInjectionParameters($values);
    }
}
