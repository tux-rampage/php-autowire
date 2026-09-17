<?php
declare(strict_types=1);

namespace TuxRampage\Autowire;

use Psr\Container\ContainerInterface;

/**
 * Represents an injectable dependency
 */
interface Injectable
{
    public function provideValue(ContainerInterface $container): mixed;
}
