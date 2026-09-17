<?php
declare(strict_types=1);

namespace TuxRampage\Autowire\Resolver;

use TuxRampage\Autowire\Injectable;

final readonly class ResolvedInstance
{
    /**
     * @param string $className
     * @param array<string, Injectable>|list<Injectable> $injections
     */
    public function __construct(
        public string $className,
        public array $injections,
    ) {
    }

}
