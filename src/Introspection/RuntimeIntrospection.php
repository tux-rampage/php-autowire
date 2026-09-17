<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use Override;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use TuxRampage\Autowire\Injectable\ScalarValue;
use TuxRampage\Autowire\Introspection\Type\IntersectionType;
use TuxRampage\Autowire\Introspection\Type\UnionType;

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
    /**
     * @var array<string, TypeDefinition>
     */
    private array $cache = [];

    #[Override]
    public function hasType(string $type): bool
    {
        return class_exists($type) || interface_exists($type) || enum_exists($type);
    }

    #[Override]
    public function introspect(string $type): TypeDefinition
    {
        $introspected = $this->cache[$type] ?? null;

        if ($introspected === null) {
            $introspected = $this->introspectClass($type);
            $this->cache[$type] = $introspected;
        }

        return $introspected;
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
    private function mapParameter(string $className, ReflectionParameter $reflected): Parameter
    {
        $default = false;

        if ($reflected->isDefaultValueAvailable()) {
            $defaultValue = $reflected->getDefaultValue();
            $default = is_scalar($defaultValue)
                ? new ScalarValue($defaultValue)
                : true;
        }

        return new Parameter(
            $className,
            $reflected->getName(),
            $this->mapType($reflected->getType()),
            $default,
            $reflected->isVariadic(),
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

            if ($parameter->isVariadic) {
                $variadic = $parameter;
            } else {
                $parameters[] = $parameter;
            }
        }

        return new ParameterList($className, $parameters, $variadic);
    }

    private function introspectClass(string $type): TypeDefinition
    {
        $reflection = new ReflectionClass($type);
        $parents = $this->collectParents($reflection);
        $parameters = $this->collectParameters($reflection);

        return $reflection->isInstantiable()
            ? new ConstructableClass($reflection->getName(), $parameters, ...$parents)
            : new ClassDefinition($reflection->getName(), $parameters, ...$parents);
    }
}
