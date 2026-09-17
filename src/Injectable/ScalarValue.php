<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Injectable;

/**
 * @template T of string|int|float|bool|null
 * @extends StaticValue<T>
 */
final readonly class ScalarValue extends StaticValue
{
    public function __construct(string|int|float|bool|null $value)
    {
        parent::__construct($value);
    }

    /**
     * @template V of string|int|float|bool|null
     * @param array{value: V} $state
     * @return self<V>
     */
    public static function __set_state(array $state): self
    {
        return new self($state['value']);
    }
}
