<?php
declare(strict_types=1);

namespace TuxRampage\Autowire\Attributes;

use Attribute;
use TuxRampage\Autowire\Injectable;
use TuxRampage\Autowire\Injectable\ContainerService;

/**
 * Attribute to inject a dependency for a constructor parameter
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class Inject
{
    /**
     * @param string $name The name of the dependency in the container to inject
     */
    public function __construct(
        public string $name = ''
    ) {
    }

    public function toInjectable(): Injectable
    {
        return new ContainerService($this->name);
    }
}
