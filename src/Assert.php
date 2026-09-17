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
     * @param callable(T):R $function The delegated callable
     * @param int $argument The input argument to pass to the delegated callable
     * @return Closure():R
     */
    public static function toPredicate(callable $function, int $argument = 0): Closure
    {
        return static fn (mixed ...$args) => $function(array_values($args)[$argument] ?? null);
    }

    /**
     * Helper to create a key predicate closure for array_any/array_all
     *
     * @psalm-pure
     * @param callable(string|int):bool $function The delegated callable
     * @return Closure(mixed, string|int):bool
     */
    public static function keyPredicate(callable $function): Closure
    {
        return self::toPredicate($function, 1);
    }

    /**
     * Assert that a given array is a hash map containing only string keys
     *
     * @template T
     * @param array<array-key, T> $values
     * @psalm-assert array<string, T> $values
     * @psalm-pure
     */
    public static function map(array $values, string|null $message = null): void
    {
        if (array_any($values, self::keyPredicate(is_int(...)))) {
            throw new InvalidArgumentException($message ?? 'A hash map must not have numeric keys');
        }
    }

    /**
     * Assert that a given array is either a hash map containing only string keys or a numerically indexed array
     *
     * @template T
     * @param array<array-key, T> $values
     * @psalm-assert array<string, T>|array<int, T> $values
     * @psalm-pure
     */
    public static function mapOrArray(array $values, string|null $message = null): void
    {
        if (!array_all($values, self::keyPredicate(is_int(...))) && !array_all($values, self::keyPredicate(is_string(...)))) {
            throw new InvalidArgumentException($message ?? 'Expected the array to be either be a hash map or a numerically indexed, got an array with mixed keys.');
        }
    }

    /**
     * Assert that the given value is a hash map of string → Injectable pairs
     *
     * @template Nullable of bool
     * @psalm-assert (Nullable is true ? array<string, Injectable|null> : array<string, Injectable>) $value
     * @psalm-pure
     */
    public static function injectableMap(mixed $values, bool $nullable = false): void
    {
        $predicate = static fn (mixed $value, int|string $key) => is_string($key)
            && ($value instanceof Injectable || ($nullable && $value === null));

        if (!is_array($values)) {
            throw new InvalidArgumentException(sprintf('Injectable map is expected to be an array, got %s', get_debug_type($values)));
        }

        if (!array_all($values, $predicate)) {
            throw new InvalidArgumentException('Injectable map does not contain only string → Injectable pairs');
        }
    }

}
