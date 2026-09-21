<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Resolver;

interface ContainerValidator
{
    public function has(string $id): bool;
}
