<?php

namespace TuxRampage\Autowire\Resolver\Preference;

use Closure;
use TuxRampage\Autowire\Injectable;
use TuxRampage\Autowire\Introspection\IntrospectionStrategy;
use TuxRampage\Autowire\Introspection\Parameter;
use TuxRampage\Autowire\Introspection\ProvidesDefaultService;
use TuxRampage\Autowire\Introspection\ProvidesPreferences;
use TuxRampage\Autowire\Introspection\Type\BuiltinType;
use TuxRampage\Autowire\Introspection\Type\ClassName;
use TuxRampage\Autowire\Introspection\Type\IntersectionType;
use TuxRampage\Autowire\Introspection\Type\UnionType;
use TuxRampage\Autowire\Introspection\TypeDefinition;


/**
 * Resolves preferences for parameters based on introspection of the context type and its supertypes.
 */
final readonly class IntrospectionResolver implements PreferenceResolver
{
    public function __construct(private IntrospectionStrategy $introspection)
    {
    }

    public function resolvePreference(TypeDefinition $contextType, Parameter $parameter): Injectable|null
    {
        $typeName = $this->resolvePreferredTypeName($contextType, $parameter->type);

        if ($typeName !== null) {
            return new Injectable\ContainerService($typeName);
        }

        foreach ($contextType->getSupertypes() as $parent) {
            $candidate = $this->resolvePreferredTypeName($this->introspection->introspect($parent), $parameter->type);

            if ($candidate !== null) {
                return new Injectable\ContainerService($candidate);
            }
        }

        $default = $this->resolveTypeDefault($contextType, $parameter->type);

        return $default !== null
            ? new Injectable\ContainerService($default)
            : null;
    }

    /**
     * @param TypeDefinition $contextType
     * @param IntersectionType|UnionType $type
     * @param Closure(TypeDefinition, BuiltinType|ClassName|IntersectionType):(string|null) $resolveType
     * @return string|null
     */
    private function resolvePreferredComplexTypeName(
        TypeDefinition $contextType,
        IntersectionType|UnionType $type,
        Closure $resolveType,
    ): string|null {
        foreach ($type->types as $subType) {
            $option = $resolveType($contextType, $subType);

            if ($option !== null) {
                // For intersection types, we need to ensure that the option satisfies all subtypes
                if ($type instanceof IntersectionType) {
                    $optionType = $this->introspection->hasType($option)
                        ? $this->introspection->introspect($option)
                        : null;

                    if (!$optionType?->satisfies($type)) {
                        continue;
                    }
                }

                return $option;
            }
        }

        // No preference for complex type
        return null;
    }

    private function resolvePreferredTypeName(TypeDefinition $contextType, BuiltinType|ClassName|IntersectionType|UnionType $type): string|null
    {
        if ($type instanceof BuiltinType) {
            return null; // No preference for built-in types
        }

        if (!$type instanceof ClassName) {
            return $this->resolvePreferredComplexTypeName($contextType, $type, $this->resolvePreferredTypeName(...));
        }

        $className = $type->toClassName();

        if ($contextType instanceof ProvidesPreferences) {
            $option = $contextType->getPreferences()->offsetGet($className);

            if ($option !== null) {
                return $option;
            }
        }

        $typeInfo = $this->tryIntrospect($className);

        return $typeInfo instanceof ProvidesDefaultService
            ? $typeInfo->getDefaultService()
            : null;
    }

    private function resolveTypeDefault(TypeDefinition $contextType, BuiltinType|ClassName|IntersectionType|UnionType $type)
    {
        if ($type instanceof UnionType || $type instanceof IntersectionType) {
            return $this->resolvePreferredComplexTypeName($contextType, $type, $this->resolveTypeDefault(...));
        }

        $typeInfo = $this->tryIntrospect($type->toClassName());

        return $typeInfo instanceof ProvidesDefaultService
            ? $typeInfo->getDefaultService()
            : null;
    }

    private function tryIntrospect(string $className): TypeDefinition|null
    {
        return $this->introspection->hasType($className)
            ? $this->introspection->introspect($className)
            : null;
    }
}