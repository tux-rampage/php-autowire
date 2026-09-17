<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Preference
{
    /**
     * @var non-empty-list<string>
     */
    public array $options;

    public function __construct(
        string $name,
        string ...$options,
    ) {
        $this->options = [$name, ...array_values($options)];
    }

    public function concat(self $other): self
    {
        return new self(
            ...$this->options,
            ...$other->options,
        );
    }
}
