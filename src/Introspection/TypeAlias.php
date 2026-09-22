<?php
declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use Override;
use TuxRampage\Autowire\Introspection\Type\ClassName;
use TuxRampage\Autowire\Introspection\Type\IntersectionType;
use TuxRampage\Autowire\Introspection\Type\UnionType;
use TuxRampage\Autowire\ReadonlyMap;

/**
 * Implements a type definition for class aliases
 */
final readonly class TypeAlias implements TypeDefinition, ProvidesPreferences, Constructable, ProvidesDefaultService
{
    public function __construct(
        private string $name,
        private TypeDefinition&Constructable&ProvidesPreferences $class,
    ) {
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getSupertypes(): array
    {
        return [
            $this->class->getName(),
            ...$this->getSupertypes(),
        ];
    }

    #[Override]
    public function getPreferences(): ReadonlyMap
    {
        return $this->class->getPreferences();
    }

    #[Override]
    public function getDefaultService(): string | null
    {
        return $this->class instanceof ProvidesDefaultService
            ? $this->class->getDefaultService()
            : null;
    }

    #[Override]
    public function toConstructableName(): string
    {
        return $this->class->toConstructableName();
    }

    #[Override]
    public function getParameters(): ParameterList
    {
        return $this->class->getParameters();
    }

    #[Override]
    public function buildConstructorParameters(array $values): array
    {
        return $this->class->buildConstructorParameters($values);
    }

    #[Override]
    public function satisfies(IntersectionType|ClassName|UnionType $type): bool
    {
        return $this->class->satisfies($type);
    }
}
