<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Injectable;

use InvalidArgumentException;
use Override;
use Psr\Container\ContainerInterface;
use TuxRampage\Autowire\Injectable;

use function array_any;
use function array_is_list;
use function array_map;

final readonly class VariadicValues implements Injectable
{
    /**
     * @param array<string, Injectable>|list<Injectable> $values
     */
    public function __construct(
        public array $values
    ) {
        if (array_any($this->values, static fn(Injectable $injectable): bool => $injectable instanceof self)) {
            throw new InvalidArgumentException('VariadicValues cannot be nested');
        }
    }

    public static function __set_state(array $state): object
    {
        return new self($state['values']);
    }

    public function requirePositional(): bool
    {
        return !$this->empty() && array_is_list($this->values);
    }

    public function empty(): bool
    {
        return count($this->values) === 0;
    }

    /**
     * @param ContainerInterface $container
     * @return array<string, mixed>|list<mixed>
     */
    #[Override]
    public function provideValue(ContainerInterface $container): array
    {
        return array_map(
            static fn(Injectable $injectable): mixed => $injectable->provideValue($container),
            $this->values,
        );
    }
}
