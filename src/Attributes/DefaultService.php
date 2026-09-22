<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Attributes;

use Attribute;

/**
 * Use this attribute on interfaces and classes to define a default service to use for autowiring this type.
 *
 * ```
 * #[DefaultService(DefaultImplementation::class)]
 * interface MyInterface
 * {}
 * ```
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class DefaultService
{
    public function __construct(
        public string $service
    ) {
    }
}
