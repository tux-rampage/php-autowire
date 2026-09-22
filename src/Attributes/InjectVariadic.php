<?php
declare(strict_types=1);

namespace TuxRampage\Autowire\Attributes;

use Attribute;
use TuxRampage\Autowire\Assert;
use TuxRampage\Autowire\Injectable;

/**
 * Use this attribute to inject dependencies for a variadic constructor parameter
 *
 * This may only be used on a variadic parameter. Any `#[Inject]` attribute is ignored, when this is attribute
 * is present.
 *
 * ```
 * class MyService
 * {
 *     public function __construct(
 *         #[InjectVariadic([
 *             new Inject(ServiceA::class),
 *             new Inject(ServiceB::class),
 *         ])]
 *         SomeInterface ...$someValue,
 *     )
 *     {}
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class InjectVariadic
{
    /**
     * @param array<string, Inject>|list<Inject> $values The map or list of values to inject
     */
    public function __construct(
        public array $values,
    ) {
    }

    public function toInjectable(): Injectable\VariadicValues
    {
        return new Injectable\VariadicValues(
            array_map(
                static fn (Inject $inject): Injectable => $inject->toInjectable(),
                $this->values
            ),
        );
    }
}
