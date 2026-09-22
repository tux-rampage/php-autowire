<?php
declare(strict_types=1);

namespace TuxRampage\Autowire\Attributes;

use Attribute;
use TuxRampage\Autowire\Injectable;

/**
 * Use this attribute to inject a dependency for a constructor parameter
 *
 * ```
 * class MyService
 * {
 *     public function __construct(
 *         #[Inject(ConcreteService::class)]
 *         public SomeService $someService,
 *         #[Inject(ProviderService::class, 'getValue')]
 *         public string $someValue,
 *     )
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class Inject
{
    /**
     * @param string $name The name of the dependency in the container to inject
     * @param string|null $providerMethod The name of the method to call on the dependency object to provide the actual value
     */
    public function __construct(
        public string $name,
        public string|null $providerMethod = null
    ) {
    }

    public function toInjectable(): Injectable
    {
        return $this->providerMethod === null
            ? new Injectable\ContainerService($this->name)
            : new Injectable\ProviderValue($this->name, $this->providerMethod);
    }
}
