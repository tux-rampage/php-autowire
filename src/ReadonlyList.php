<?php

declare(strict_types=1);

namespace TuxRampage\Autowire;

use ArrayIterator;
use Countable;
use IteratorAggregate;

use Override;
use Traversable;

use function array_values;

/**
 * @template T
 * @implements IteratorAggregate<int, T>
 */
final readonly class ReadonlyList implements IteratorAggregate, Countable
{
    /**
     * @var list<T>
     */
    private array $items;

    /**
     * @param T[] $items
     */
    public function __construct(array $items)
    {
        $this->items = array_values($items);
    }

    /**
     * @return Traversable<int, T>
     */
    #[Override]
    public function getIterator(): Traversable
    {
        yield from $this->items;
    }

    #[Override]
    public function count(): int
    {
        return count($this->items);
    }

    public function toArray(): array
    {
        return $this->items;
    }
}
