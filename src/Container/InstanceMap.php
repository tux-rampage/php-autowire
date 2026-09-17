<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Container;

use Psr\Container\ContainerInterface;

interface InstanceMap extends ContainerInterface
{
    public function set(string $id, mixed $instance): void;

    /**
     * Create a new copy of this instance map, including the given items.
     *
     * @param array|InstanceMap $items
     * @param bool $replace Wheter an item should be replaced if it already existed
     * @return InstanceMap
     */
    public function withItems(array|InstanceMap $items, bool $replace = true): InstanceMap;

    /**
     * Creates an array representation of the instance map.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
