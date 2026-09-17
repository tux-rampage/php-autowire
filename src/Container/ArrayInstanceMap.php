<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Container;

use Override;

final class ArrayInstanceMap implements InstanceMap
{
    /**
     * @param array<string, mixed> $instances
     */
    public function __construct(private array $instances = [])
    {
    }

    #[Override]
    public function withItems(array|InstanceMap $items, bool $replace = true): InstanceMap
    {
        $newItems = $items instanceof InstanceMap ? $items->toArray() : $items;

        return clone($this, [
            'instances' => $replace
                ? [...$this->instances, ...$newItems]
                : [...$newItems, ...$this->instances],
        ]);
    }

    #[Override]
    public function toArray(): array
    {
        return $this->instances;
    }

    #[Override]
    public function set(string $id, mixed $instance): void
    {
        $this->instances[$id] = $instance;
    }

    #[Override]
    public function get(string $id): mixed
    {
        return $this->instances[$id] ?? null;
    }

    #[Override]
    public function has(string $id): bool
    {
        return isset($this->instances[$id]);
    }
}
