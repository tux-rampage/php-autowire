<?php declare(strict_types=1);

namespace TuxRampage\Autowire;

use Closure;

use InvalidArgumentException;

use function array_all;
use function array_any;
use function array_first;
use function array_values;
use function get_debug_type;
use function is_array;
use function is_string;

abstract class Assert
{
    /**
     * Helper to create a predicate closure from a callable
     *
     * This will create a closure that will pass a single specific input argument to the delegated
     * callable.
     *
     * @psalm-pure
     * @template T
     * @template R
     * @template C of callable(T):R
     * @param C $function The delegated callable
     * @param int $argument The input argument to pass to the delegated callable
     * @return (C is (pure-callable(T):R) ? (pure-Closure():R) : (Closure():R))
     */
    public static function toPredicate(callable $function, int $argument = 0): Closure
    {
        /**
         * @psalm-suppress MixedArgument
         * @psalm-suppress PossiblyNullArgument
         */
        return static fn (mixed ...$args) => $function(array_values($args)[$argument] ?? null);
    }

    /**
     * Helper to create a key predicate closure for array_any/array_all
     *
     * @psalm-pure
     * @template C of callable(string|int):bool
     * @param C $function The delegated callable
     * @return (C is (pure-callable(string|int):bool) ? (pure-Closure(mixed, string|int):bool) : (Closure(mixed, string|int):bool))
     */
    public static function keyPredicate(callable $function): Closure
    {
        return self::toPredicate($function, 1);
    }

    /**
     * Assert that a given array is a hash map containing only string keys and values
     *
     * @psalm-assert array<string, string> $values
     * @psalm-pure
     */
    public static function stringMap(mixed $values, string|null $noArrayMessage = null, string|null $badItemMessage = null): void
    {
        if (!is_array($values)) {
            throw new InvalidArgumentException($noArrayMessage ?? 'Value is not an array');
        }

        if (array_any($values, static fn (mixed $value, mixed $key): bool => !is_string($key) || !is_string($value))) {
            throw new InvalidArgumentException($badItemMessage ?? 'Hashmap contains invalid item');
        }
    }

    /**
     * Assert that the given value is a hash map of string → Injectable pairs
     *
     * @template Nullable of bool
     * @psalm-assert (Nullable is true ? array<string, Injectable|null> : array<string, Injectable>) $values
     * @psalm-pure
     * @param Nullable $nullable
     */
    public static function injectableMap(mixed $values, bool $nullable = false): void
    {
        $predicate = static fn (mixed $value, int|string $key): bool => is_string($key)
            && ($value instanceof Injectable || ($nullable && $value === null));

        if (!is_array($values)) {
            throw new InvalidArgumentException(sprintf('Injectable map is expected to be an array, got %s', get_debug_type($values)));
        }

        if (!array_all($values, $predicate)) {
            throw new InvalidArgumentException('Injectable map does not contain only string → Injectable pairs');
        }
    }

}
