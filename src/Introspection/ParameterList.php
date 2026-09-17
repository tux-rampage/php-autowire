<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use ArrayIterator;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;
use LogicException;
use TuxRampage\Autowire\Assert;
use TuxRampage\Autowire\Injectable;
use UnexpectedValueException;

use function array_any;
use function array_reduce;
use function array_values;
use function is_int;

final readonly class ParameterList implements IteratorAggregate
{
    /**
     * @var array<string, Parameter>
     */
    private array $items;

    /**
     * @param class-string $className
     * @param array<Parameter> $items
     * @param Parameter|null $variadicParameter
     */
    public function __construct(
        private string $className,
        array $items = [],
        public Parameter|null $variadicParameter = null,
    ) {
        $this->items = array_reduce(
            $items,
            /**
             * @param array<string, Parameter> $carry
             * @return array<string, Parameter>
             */
            static fn (array $carry, Parameter $parameter): array => [...$carry, $parameter->name => $parameter],
            [],
        );
    }

    public function getParameter(string $name): Parameter
    {
        return $this->items[$name] ?? throw new InvalidArgumentException(sprintf('Class "%s" does not have a parameter named "%s"', $this->className, $name));
    }

    /**
     * @return Iterator<Parameter>
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Build constructor parameters for a constructable item
     *
     * This will put all parameters in order and force positional parameters when a variadic parameter with numeric
     * keys is present.
     *
     * @param array<string, Injectable> $values
     *
     * @throws UnexpectedValueException When the variadic parameter contains mixed numeric and string keys
     * @throws LogicException When a parameter is required, but no value is provided
     *
     * @return array<string, Injectable>|list<Injectable>
     */
    public function buildInjectionParameters(array $values): array
    {
        $injections = [];
        $variadic = $this->variadicParameter ? ($values[$this->variadicParameter->name] ?? []) : [];
        $requirePositional = array_any($variadic, Assert::keyPredicate(is_int(...)));

        Assert::mapOrArray($variadic, sprintf('Variadic constructor parameter "%s" for "%s" must not contain mixed numeric and string keys', $this->variadicParameter?->name ?? '_', $this->className));

        foreach ($this->items as $parameter) {
            $injection = $values[$parameter->name] ?? null;

            if ($injection === null) {
                if (!$requirePositional && $parameter->isOptional()) {
                    continue;
                }

                $injection = $parameter->toDefaultInjection();
            }

            $injections[$parameter->name] = $injection;
        }

        if (!$requirePositional) {
            return [...$injections, ...$variadic];
        }

        return [
            ...array_values($injections),
            ...array_values($variadic),
        ];
    }

}
