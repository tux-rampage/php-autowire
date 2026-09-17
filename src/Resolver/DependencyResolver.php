<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Resolver;

use TuxRampage\Autowire\Injectable;

interface DependencyResolver
{
    /**
     * @param string $className
     * @param array<string, Injectable> $injections
     * @return ResolvedInstance
     */
    public function resolve(string $className, array $injections): ResolvedInstance;
}
