<?php
declare(strict_types=1);

namespace TuxRampage\Autowire\Resolver\Preference;

use ArrayObject;
use Override;
use Psr\Container\ContainerInterface;
use TuxRampage\Autowire\Config\AliasConfig;
use TuxRampage\Autowire\Config\TypeConfig;
use TuxRampage\Autowire\Configuration;
use TuxRampage\Autowire\Injectable;
use TuxRampage\Autowire\Introspection\ClassDefinition;
use TuxRampage\Autowire\Introspection\IntrospectionStrategy;
use TuxRampage\Autowire\Introspection\Parameter;
use TuxRampage\Autowire\Introspection\Type\BuiltinType;
use TuxRampage\Autowire\Introspection\Type\ClassName;
use TuxRampage\Autowire\Introspection\Type\IntersectionType;
use TuxRampage\Autowire\Introspection\Type\UnionType;
use TuxRampage\Autowire\Introspection\TypeDefinition;
use TuxRampage\Autowire\Introspection\VariadicParameter;
use TuxRampage\Autowire\ReadonlyMap;
use TuxRampage\Autowire\Resolver\ContainerValidator;

/**
 * Resolves preferences for types based on the provided configuration and container.
 *
 * @api
 */
final readonly class ConfigResolver implements PreferenceResolver
{
    public function __construct(
        private ContainerValidator|ContainerInterface $container,
        private IntrospectionStrategy $introspection,
        private Configuration $config,
    ) {
    }

    /**
     * Finds the preference option for the given requested type from the provided options.
     *
     * @param ClassName|IntersectionType|UnionType $requestedType The type to resolve the preference for
     * @param ReadonlyMap<string> $options The available preference options
     * @return string|null The resolved preference option or null if none found
     */
    public function findPreferenceOption(ClassName|IntersectionType|UnionType $requestedType, ReadonlyMap $options): string | null
    {
        if ($requestedType instanceof ClassName) {
            return $options->offsetGet($requestedType->toClassName());
        }

        // This is a complex type (intersection or union), so we need to check each type
        foreach ($requestedType->types as $typeCandidate) {
            if ($typeCandidate instanceof BuiltinType) {
                continue; // Skip built-in types as they cannot be resolved by preference
            }

            $preference = $this->findPreferenceOption($typeCandidate, $options);

            // For intersection type we need to ensure that all types are fulfilled by the preference
            if ($preference !== null && $requestedType instanceof IntersectionType) {
                $optionType = $this->introspection->hasType($preference)
                    ? $this->introspection->introspect($preference)
                    : null;

                if ($optionType?->satisfies($requestedType)) {
                    return $preference;
                }

                continue;
            }

            if ($preference !== null) {
                return $preference;
            }
        }

        return null;
    }

    public function findPreference(AliasConfig|TypeConfig $config, ClassName|IntersectionType|UnionType $requestedType): string | null
    {
        $config = $config instanceof AliasConfig ? $config->type : $config;
        return $this->findPreferenceOption($requestedType, $config->preferences);
    }

    private function shouldInherit(AliasConfig|TypeConfig $config): bool
    {
        return $config instanceof TypeConfig
            ? $config->inherit
            : $config->type->inherit;
    }

    #[Override]
    public function resolvePreference(TypeDefinition $contextType, Parameter $parameter): Injectable|null
    {
        $requestedType = $parameter->type;

        // Variadic parameters or built-in types cannot be resolved by preference
        if ($parameter instanceof VariadicParameter || $requestedType instanceof BuiltinType) {
            return null;
        }

        $config = $this->config->getTypeConfig($contextType->getName());

        if ($config === null) {
            return null;
        }

        $preference = $this->findPreference($config, $requestedType);
        $inherit = $this->shouldInherit($config) || $preference === '*';

        if ($preference !== null && $preference !== '*' && $this->container->has($preference)) {
            return new Injectable\ContainerService($preference);
        }

        foreach ($contextType->getSupertypes() as $parent) {
            if (!$inherit) {
                return null;
            }

            $config = $this->config->getTypeConfig($parent);

            if ($config === null) {
                continue;
            }

            $preference = $this->findPreference($config, $requestedType);
            $inherit = $this->shouldInherit($config) || $preference === '*';

            if ($preference !== null && $preference !== '*' && $this->container->has($preference)) {
                $candidate = $this->tryIntrospect($preference);

                if ($candidate?->satisfies($requestedType)) {
                    return new Injectable\ContainerService($preference);
                }
            }
        }

        $preference = $this->findPreferenceOption($requestedType, $this->config->preferences);

        return $preference
            ? new Injectable\ContainerService($preference)
            : null;
    }

    private function tryIntrospect(string $className): TypeDefinition|null
    {
        return $this->introspection->hasType($className)
            ? $this->introspection->introspect($className)
            : null;
    }
}
