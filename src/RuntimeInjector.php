<?php

declare(strict_types=1);

namespace TuxRampage\Autowire;

use LogicException;
use Override;
use Psr\Container\ContainerInterface;
use TuxRampage\Autowire\Container\ArrayInstanceMap;
use TuxRampage\Autowire\Container\InstanceMap;
use TuxRampage\Autowire\Introspection\IntrospectionStrategy;

use TuxRampage\Autowire\Resolver\DependencyResolver;
use function array_map;
use function assert;
use function class_exists;
use function is_array;

/**
 * @api
 */
final class RuntimeInjector implements Injector
{
    public function __construct(
        private readonly IntrospectionStrategy $introspection,
        private readonly DependencyResolver $resolver,
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

    /**
     * @template T  of object
     * @param class-string<T>|string $class
     * @param array<string, Injectable> $args
     * @return T
     */
    #[Override]
    public function createInstance(string $class, array $args = []): object
    {
        $resolved = $this->resolver->resolve($class, $args);
        $constructable = $resolved->className;
        $container = $this->container;
        $args = array_map(
            static fn (Injectable $injectable): mixed => $injectable->provideValue($container),
            $resolved->injections,
        );

        assert(class_exists($constructable));

        /**
         * @psalm-suppress MixedMethodCall
         * @psalm-var T
         */
        return new $constructable(...$args);
    }
}
