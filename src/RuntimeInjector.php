<?php

declare(strict_types=1);

namespace TuxRampage\Autowire;

use LogicException;
use Override;
use Psr\Container\ContainerInterface;
use TuxRampage\Autowire\Container\ArrayInstanceMap;
use TuxRampage\Autowire\Container\InstanceMap;
use TuxRampage\Autowire\Introspection\IntrospectionStrategy;

use function is_array;

final class RuntimeInjector implements Injector
{
    public function __construct(
        private readonly IntrospectionStrategy $introspection,
        private ContainerInterface $container,
    ) {
    }

    /**
     * @api
     * @param ContainerInterface|null $container
     * @param array|InstanceMap $instances
     * @return ContainerInterface
     */
    public function decorateContainer(ContainerInterface|null $container = null, array|InstanceMap $instances = []): ContainerInterface
    {
        $container ??= $this->container;

        if ($container instanceof Container && $container->usesInjector($this)) {
            $decorated = $instances ? $container->withInstances($instances) : $container;
        } else {
            $decorated = new Container($this, $container, $instances);
        }

        $this->container = $decorated;
        return $decorated;
    }

    #[Override]
    public function canCreate(string $class): bool
    {
        if (!$this->introspection->hasType($class)) {
            return false;
        }

        $type = $this->introspection->introspect($class);
        return $type instanceof Introspection\Constructable;
    }

    #[Override]
    public function createInstance(string $class, array $args = []): object
    {


        // TODO: Implement createInstance() method.
    }
}
