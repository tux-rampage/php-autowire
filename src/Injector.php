<?php

declare(strict_types=1);

namespace TuxRampage\Autowire;

use Psr\Container\ContainerInterface;

interface Injector
{
    /**
     * Whether the injector can create an instance of the given class or type alias.
     */
    public function canCreate(string $class): bool;

    /**
     * @template T  of object
     * @param class-string<T>|string $class
     * @param array<string, Injectable> $args
     * @return T
     */
    public function createInstance(string $class, array $args = []): object;
}
