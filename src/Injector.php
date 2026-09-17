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
     * @template T
     * @template C of class-string<T>|string
     * @param C $class
     * @param array<string, Injectable> $args
     * @return (C is class-string<T> ? T : object)
     */
    public function createInstance(string $class, array $args = []): object;
}
