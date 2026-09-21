<?php
declare(strict_types=1);

namespace TuxRampage\Autowire\Resolver\Preference;

use ArrayObject;
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
use TuxRampage\Autowire\Resolver\ContainerValidator;

final readonly class ConfigResolver implements PreferenceResolver
{
    public function __construct(
        private ContainerValidator|ContainerInterface $container,
        private IntrospectionStrategy $introspection,
        private Configuration $config,
    ) {
    }

    public function findPreference(AliasConfig|TypeConfig $config, ClassName|IntersectionType|UnionType $requestedType): string | null
    {
        $config = $config instanceof AliasConfig ? $config->type : $config;

        if ($requestedType instanceof ClassName) {
            return $config->preferences->offsetGet($requestedType->toClassName());
        }

        foreach ($requestedType->types as $typeCandidate) {
            $preference = $this->findPreference($config, $typeCandidate);

            if ($preference !== null) {
                return $preference;
            }
        }

        return null;
    }

    public function resolvePreference(TypeDefinition $contextType, Parameter $parameter): Injectable|null
    {
        $requestedType = $parameter->type;

        if ($requestedType instanceof BuiltinType) {
            return null;
        }

        $config = $this->config->getTypeConfig($contextType->getName());
        $preference = $this->findPreference($config, $requestedType);
        $inherit = $config->inherit || $preference === '*';

        if ($preference !== null && $preference !== '*' && $this->container->has($preference)) {
            return new Injectable\ContainerService($preference);
        }

        foreach ($contextType->getSupertypes() as $parent) {
            if (!$inherit) {
                return null;
            }

            $config = $this->config->getTypeConfig($parent);
            $preference = $this->findPreference($config, $requestedType);
            $inherit = $config->inherit || $preference === '*';

            if ($preference !== null && $preference !== '*' && $this->container->has($preference)) {
                $candidate = $this->introspection->introspect($preference);

                if ($candidate->satisfies($requestedType)) {
                    return new Injectable\ContainerService($preference);
                }
            }
        }

        return null;
    }
}
