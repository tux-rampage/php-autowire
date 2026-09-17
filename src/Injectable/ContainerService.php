<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Injectable;

use Override;
use Psr\Container\ContainerInterface;
use TuxRampage\Autowire\Injectable;

/**
 * Represents a dependency resolved from the container
 */
final readonly class ContainerService implements Injectable
{
    public function __construct(private string $name)
    {
    }

    /**
     * @param array{name: string} $state
     * @return self
     */
    public static function __set_state(array $state): self
    {
        return new self($state['name']);
    }

    #[Override]
    public function provideValue(ContainerInterface $container): mixed
    {
        return $container->get($this->name);
    }
}
