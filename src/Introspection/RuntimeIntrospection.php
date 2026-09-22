<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use Override;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use TuxRampage\Autowire\Attributes\Inject;
use TuxRampage\Autowire\Attributes\TypePreference;
use TuxRampage\Autowire\Injectable;
use TuxRampage\Autowire\Injectable\ScalarValue;
use TuxRampage\Autowire\Injectable\VariadicValues;
use TuxRampage\Autowire\Introspection\Type\IntersectionType;
use TuxRampage\Autowire\Introspection\Type\UnionType;

use TuxRampage\Autowire\ReadonlyMap;

use function array_first;
use function array_map;
use function array_reduce;
use function array_values;
use function class_exists;
use function enum_exists;
use function get_class;
use function is_scalar;

/**
 * Implements the introspection during runtime via reflection
 */
final class RuntimeIntrospection implements IntrospectionStrategy
{
    #[Override]
    public function hasType(string $type): bool
    {
        return class_exists($type) || interface_exists($type) || enum_exists($type);
    }

    #[Override]
    public function introspect(string $type): TypeDefinition
    {
        return $this->introspectClass($type);
    }

    /**
     * @param ReflectionClass $reflection
     * @return list<string>
     */
    private function collectParents(ReflectionClass $reflection): array
    {
        $parents = $reflection->getInterfaceNames();
        $parent = $reflection->getParentClass();

        while ($parent) {
            $parents[] = $parent->getName();
            $parent = $parent->getParentClass();
        }

        return array_values($parents);
    }
    private function mapType(ReflectionNamedType|UnionType|IntersectionType|null $type): Type\UnionType|Type\IntersectionType|Type\BuiltinType|Type\ClassName
    {
        if ($type === null) {
            return new Type\BuiltinType('mixed');
        }

        return match (get_class($type)) {
            ReflectionUnionType::class => new Type\UnionType(...array_map($this->mapType(...), $type->getTypes())),
            ReflectionIntersectionType::class => new Type\IntersectionType(...array_map($this->mapType(...), $type->getTypes())),
            ReflectionNamedType::class => $type->isBuiltin()
                ? new Type\BuiltinType($type->getName())
                : Type\ClassName::fromClassName($type->getName()),
        };
    }

    private function reflectVariadicInjections(ReflectionParameter $reflected): VariadicValues|null
    {
        /** @var ReflectionAttribute<VariadicValues>|null $attribute */
        $attribute = array_first($reflected->getAttributes(VariadicValues::class));

        if ($attribute) {
            return $attribute->newInstance();
        }

        $injectAttributes = array_map(
            static fn(ReflectionAttribute $attribute) => $attribute->newInstance(),
            $reflected->getAttributes(Inject::class),
        );

        return $injectAttributes ? new VariadicValues(array_values($injectAttributes)) : null;
    }

    private function mapParameter(string $className, ReflectionParameter $reflected): Parameter
    {
        $default = false;

        if ($reflected->isDefaultValueAvailable()) {
            $defaultValue = $reflected->getDefaultValue();
            $default = is_scalar($defaultValue)
                ? new ScalarValue($defaultValue)
                : true;
        }

        $type = $this->mapType($reflected->getType());

        if ($reflected->isVariadic()) {
            return new VariadicParameter(
                $className,
                $reflected->getName(),
                $type,
                $this->reflectVariadicInjections($reflected),
            );
        }

        /** @var ReflectionAttribute<Inject>|null $attribute */
        $attribute = array_first($reflected->getAttributes(Inject::class));

        return new PositionalParameter(
            $className,
            $reflected->getName(),
            $this->mapType($reflected->getType()),
            $default,
            $attribute?->newInstance(),
        );
    }

    private function collectParameters(ReflectionClass $class): ParameterList
    {
        $constructor = $class->getConstructor();
        $className = $class->getName();

        if (!$constructor) {
            return new ParameterList($className);
        }

        $parameters = [];
        $variadic = null;

        foreach ($constructor->getParameters() as $reflectedParameter) {
            $parameter = $this->mapParameter($className, $reflectedParameter);

            if ($parameter instanceof VariadicParameter) {
                $variadic = $parameter;
            } else {
                $parameters[] = $parameter;
            }
        }

        return new ParameterList($className, $parameters, $variadic);
    }

    /**
     * @param ReflectionClass $class
     * @return ReadonlyMap<string>
     */
    private function reflectPreferences(ReflectionClass $class): ReadonlyMap
    {
        $attributes = array_reduce(
            array_map(
                static fn(ReflectionAttribute $attribute):TypePreference => $attribute->newInstance(),
                $class->getAttributes(TypePreference::class)
            ),
            /**
             * @param array<string, string> $carry
             * @return array<string, string>
             */
            static fn(array $carry, TypePreference $attribute):array => [
                ...$carry,
                $attribute->type => $attribute->prefer
            ],
            []
        );

        return new ReadonlyMap($attributes);
    }

    private function introspectClass(string $type): TypeDefinition
    {
        $reflection = new ReflectionClass($type);
        $parents = $this->collectParents($reflection);
        $interfaces = array_values($reflection->getInterfaceNames());
        $parameters = $this->collectParameters($reflection);
        $preferences = $this->reflectPreferences($reflection);

        return $reflection->isInstantiable()
            ? new ConstructableClass($reflection->getName(), $parameters, $parents, $interfaces, $preferences)
            : new ClassDefinition($reflection->getName(), $parameters, $parents, $interfaces, $preferences);
    }
}
