<?php
declare(strict_types=1);

namespace TuxRampage\Autowire\Injectable;

use Override;
use Psr\Container\ContainerInterface;
use TuxRampage\Autowire\Injectable;

/**
 * @template T
 */
abstract readonly class StaticValue implements Injectable
{
    /**
     * @param T $value
     */
    public function __construct(public mixed $value)
    {
    }

    /**
     * @return T
     */
    #[Override]
    public function provideValue(ContainerInterface $container): mixed
    {
        return $this->value;
    }
}
