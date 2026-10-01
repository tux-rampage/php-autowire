<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Resolver;

use Override;
use RuntimeException;
use TuxRampage\Autowire\Assert;
use TuxRampage\Autowire\Config\AliasConfig;
use TuxRampage\Autowire\Configuration;
use TuxRampage\Autowire\Injectable;
use TuxRampage\Autowire\Introspection\Constructable;
use TuxRampage\Autowire\Introspection\IntrospectionStrategy;
use TuxRampage\Autowire\Introspection\Parameter;
use TuxRampage\Autowire\Introspection\ProvidesDefaultService;
use TuxRampage\Autowire\Introspection\Type\ClassName;
use TuxRampage\Autowire\Introspection\TypeDefinition;
use TuxRampage\Autowire\Resolver\Preference\PreferenceResolver;
use UnexpectedValueException;
use function get_class;
use function sprintf;


final readonly class DefaultResolver implements DependencyResolver
{
    public function __construct(
        private IntrospectionStrategy $introspection,
        private PreferenceResolver $preferenceResolver,
        private Configuration $configuration,
    ) {
    }

    private function resolveParameterByConfig(TypeDefinition $contextType, Parameter $parameter): Injectable|null
    {
        $types = [
            $contextType->getName(),
            ...$contextType->getSupertypes(),
        ];

        foreach ($types as $currentContextType) {
            $config = $this->configuration->getTypeConfig($currentContextType);
            $config = $config instanceof AliasConfig ? $config->type : $config;
            $injection = $config?->parameters->offsetGet($parameter->name);

            if ($injection || $config?->inherit === false) {
                return $injection;
            }
        }

        return null;
    }

    private function resolveParameterFallbackService(Parameter $parameter): string
    {
        $service = $parameter->type instanceof ProvidesDefaultService
            ? $parameter->type->getDefaultService()
            : null;

        if ($parameter->type instanceof ClassName) {
            $service ??= $parameter->type->toClassName();
        }

        return $service ?? throw new RuntimeException(sprintf(
            'Unable to resolve required parameter %s for class %s',
            $parameter->name,
            $parameter->className,
        ));
    }

    private function resolveParameter(TypeDefinition $contextType, Parameter $parameter): Injectable|null
    {
        $injection = $this->resolveParameterByConfig($contextType, $parameter)
            ?? $this->preferenceResolver->resolvePreference($contextType, $parameter);

        if (!$injection && !$parameter->isOptional()) {
            return new Injectable\ContainerService(
                $this->resolveParameterFallbackService($parameter),
            );
        }

        return $injection;
    }

    #[Override]
    public function resolve(string $className, array $injections): ResolvedInstance
    {
        Assert::injectableMap($injections);
        $introspected = $this->introspection->introspect($className);

        if (!$introspected instanceof Constructable) {
            throw new UnexpectedValueException(sprintf('Cannot resolve non-constructable type: %s (%s)', $className, get_class($introspected)));
        }

        $parameters = $introspected->getParameters();
        $resolved = [];

        foreach ($parameters->allParameters() as $parameter) {
            $injection = $injections[$parameter->name] ?? $this->resolveParameter($introspected, $parameter);

            unset($injections[$parameter->name]);

            if ($injection !== null) {
                $resolved[$parameter->name] = $injection;
            }
        }

        $params = $introspected->buildConstructorParameters($resolved);

        return new ResolvedInstance($className, $params);
    }
}
