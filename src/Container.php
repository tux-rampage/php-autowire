<?php
declare(strict_types=1);

namespace TuxRampage\Autowire;

use Override;
use Psr\Container\ContainerInterface;
use TuxRampage\Autowire\Container\ArrayInstanceMap;
use TuxRampage\Autowire\Container\InstanceMap;

use function array_any;
use function is_array;

/**
 * Provides a simple autowiring container
 */
final readonly class Container implements ContainerInterface
{
    private InstanceMap $instances;

    /**
     * @param array<string, mixed>|InstanceMap $instances
     */
    public function __construct(
        private Injector $injector,
        private ContainerInterface|null $delegate = null,
        array|InstanceMap $instances = [],
    ) {
        $instanceMap = $instances instanceof InstanceMap ? $instances : new ArrayInstanceMap($instances);
        $defaults = [
            Injector::class => $this->injector,
            RuntimeInjector::class => $this->injector,
            ContainerInterface::class => $this,
            self::class => $this,
        ];

        $addDefaults = array_any($defaults, fn (mixed $_, string $id) => !$instanceMap->has($id));
        $this->instances = $addDefaults
            ? $instanceMap->withItems($defaults, false)
            : $instanceMap;
    }

    /**
     * Create a new container based on this with the given instances
     *
     * @param array<string, mixed>|InstanceMap $instances When an array is given it will reuse a copy of the existing instance map, otherwise the existing instance map is replaced
     * @param bool $replace When true the existing instance map is replaced entirely, otherwise the existing instances are merged
     * @return $this
     */
    public function withInstances(array|InstanceMap $instances, bool $replace = false): self
    {
        if ($replace) {
            $newInstances = is_array($instances) ? new ArrayInstanceMap($instances) : $instances;
        } else {
            $newInstances = $instances instanceof InstanceMap
                ? $instances->withItems($this->instances, false)
                : $this->instances->withItems($instances);
        }

        return clone($this, [
            'instances' => $newInstances,
        ]);
    }

    public function usesInjector(Injector $injector): bool
    {
        return $this->injector === $injector;
    }

    #[Override]
    public function has(string $id): bool
    {
        return $this->instances->has($id)
            || $this->delegate?->has($id)
            || $this->injector->canCreate($id);
    }

    #[Override]
    public function get(string $id): mixed
    {
        if ($this->instances->has($id)) {
            return $this->instances->get($id);
        }

        if ($this->delegate?->has($id)) {
            return $this->delegate->get($id);
        }

        $instance = $this->injector->createInstance($id);
        $this->instances->set($id, $instance);

        return $instance;
    }
}
