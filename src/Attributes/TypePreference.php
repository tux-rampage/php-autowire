<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Attributes;

use Attribute;

/**
 * Use this attribute to define type preferences for dependencies when autowiring.
 *
 * This attribute may be used multiple times on a class to define multiple preferences.
 *
 * ```
 * #[TypePreference(MyDependency::class, DefaultImplementation::class)]
 * class MyService
 * {
 *     public function __construct(
 *         MyDependency $dependency
 *     ) {
 *     }
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class TypePreference
{
    public function __construct(
        public string $type,
        public string $prefer,
    ) {
    }
}
