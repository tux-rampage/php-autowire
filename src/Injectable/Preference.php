<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Injectable;

use Psr\Container\ContainerInterface;
use TuxRampage\Autowire\Injectable;

final readonly class Preference implements Injectable
{

    public function provideValue(ContainerInterface $container): mixed
    {
        // TODO: Implement provideValue() method.
    }
}
