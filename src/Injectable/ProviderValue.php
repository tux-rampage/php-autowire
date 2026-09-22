<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Injectable;

use Override;
use Psr\Container\ContainerInterface;
use TuxRampage\Autowire\Injectable;

use function is_callable;
use function is_object;

/**
 * Represents a dependency resolved from a method of a container server
 */
final readonly class ProviderValue implements Injectable
{
    public function __construct(
        public string $name,
        public string $method,
    ) {
    }

    /**
     * @param array{name: string, method: string} $state
     * @return self
     */
    public static function __set_state(array $state): self
    {
        return new self($state['name'], $state['method']);
    }

    #[Override]
    public function provideValue(ContainerInterface $container): mixed
    {
        $service = $container->get($this->name);
        assert(is_object($service) && is_callable([$service, $this->method]));

        return $service->{$this->method}();
    }
}
