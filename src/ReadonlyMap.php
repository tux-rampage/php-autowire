<?php

declare(strict_types=1);

namespace TuxRampage\Autowire;

use ArrayAccess;
use ArrayIterator;
use ArrayObject;
use Countable;
use IteratorAggregate;
use LogicException;
use Override;

use Traversable;

use function array_key_exists;
use function assert;
use function is_string;

/**
 * @template T
 * @implements ArrayAccess<string, T>
 */
final readonly class ReadonlyMap implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * @param array<string, T> $data
     */
    public function __construct(private array $data)
    {
    }

    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @return Traversable<string, T>
     */
    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    #[Override]
    public function count(): int
    {
        return count($this->data);
    }

    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        assert(is_string($offset));
        return array_key_exists($offset, $this->data);
    }

    /**
     * @param string $offset
     * @return T|null
     */
    #[Override]
    public function offsetGet(mixed $offset): mixed
    {
        assert(is_string($offset));
        return $this->data[$offset] ?? null;
    }

    /**
     * @throws LogicException This method is not allowed on a readonly map
     * @return never
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('Cannot change a readonly map');
    }

    /**
     * @throws LogicException This method is not allowed on a readonly map
     * @return never
     */
    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('Cannot change a readonly map');
    }
}
