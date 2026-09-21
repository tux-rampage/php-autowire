<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Resolver;

use Override;
use Psr\Container\ContainerInterface;
use TuxRampage\Autowire\Assert;
use TuxRampage\Autowire\Injectable;
use TuxRampage\Autowire\Introspection\Constructable;
use TuxRampage\Autowire\Introspection\IntrospectionStrategy;
use TuxRampage\Autowire\Introspection\Parameter;
use TuxRampage\Autowire\Introspection\TypeDefinition;
use UnexpectedValueException;

use function array_map;
use function get_class;
use function sprintf;

final class DefaultResolver implements DependencyResolver
{
    public function __construct(
        private readonly IntrospectionStrategy $introspection,
        private readonly ContainerInterface $container,
    )
    {
    }

    private function resolveParameter(TypeDefinition $contextType, Parameter $parameter): Injectable|null
    {
        return null;
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

        $container = $this->container;
        $params = array_map(
            static fn (Injectable $item) => $item->provideValue($container),
            $introspected->buildConstructorParameters($resolved)
        );

        return new ResolvedInstance($className, $params);
    }
}
