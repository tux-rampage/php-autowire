<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use Override;

final readonly class ConstructableClass extends ClassDefinition implements Constructable
{
    #[Override]
    public function toConstructableName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getParameters(): ParameterList
    {
        return $this->parameters;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function buildConstructorParameters(array $values): array
    {
        return $this->parameters->buildInjectionParameters($values);
    }
}
