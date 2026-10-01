<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use ArrayIterator;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;
use LogicException;
use Override;
use Psalm\Issue\Trace;
use Traversable;
use TuxRampage\Autowire\Assert;
use TuxRampage\Autowire\Injectable;
use UnexpectedValueException;

use function array_any;
use function array_reduce;
use function array_values;
use function is_int;

/**
 * @implements IteratorAggregate<string, Parameter>
 */
final readonly class ParameterList implements IteratorAggregate
{
    /**
     * @var array<string, PositionalParameter>
     */
    private array $items;

    /**
     * @param class-string $className
     * @param array<PositionalParameter> $items
     * @param VariadicParameter|null $variadicParameter
     */
    public function __construct(
        private string $className,
        array $items = [],
        public VariadicParameter|null $variadicParameter = null,
    ) {
        $this->items = array_reduce(
            $items,
            /**
             * @param array<string, PositionalParameter> $carry
             * @return array<string, PositionalParameter>
             */
            static fn (array $carry, PositionalParameter $parameter): array => [...$carry, $parameter->name => $parameter],
            [],
        );
    }

    public function getParameter(string $name): Parameter
    {
        if ($this->variadicParameter !== null && $this->variadicParameter->name === $name) {
            return $this->variadicParameter;
        }

        return $this->items[$name] ?? throw new InvalidArgumentException(sprintf('Class "%s" does not have a parameter named "%s"', $this->className, $name));
    }

    /**
     * @return Traversable<string, Parameter>
     */
    #[Override]
    public function getIterator(): Traversable
    {
        yield from $this->items;
    }

    /**
     * Returns all parameters including the variadic parameter
     *
     * @return iterable<string, Parameter>
     */
    public function allParameters(): iterable
    {
        yield from $this->items;

        if ($this->variadicParameter) {
            yield $this->variadicParameter->name => $this->variadicParameter;
        }
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
        $variadic = $this->variadicParameter ? ($values[$this->variadicParameter->name] ?? null) : null;

        if (!$variadic && $this->variadicParameter) {
            $variadic = $this->variadicParameter->toDefaultInjection();
        }

        if ($variadic && !$variadic instanceof Injectable\VariadicValues) {
            $variadic = new Injectable\VariadicValues([$variadic]);
        }

        $requirePositional = $variadic?->requirePositional() ?? false;

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

        if (!$variadic) {
            return $injections;
        }

        if (!$requirePositional) {
            return [...$injections, ...$variadic->values];
        }

        return [
            ...array_values($injections),
            ...array_values($variadic->values),
        ];
    }
}
