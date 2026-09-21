<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Config;

use TuxRampage\Autowire\ReadonlyMap;

/**
 * @psalm-import-type TypeConfigArray from TypeConfig
 */
final readonly class AliasConfig
{
    public function __construct(
        public string $name,
        public TypeConfig $type,
    ) {
    }

    /**
     * @return TypeConfigArray
     */
    public function toArray(): array
    {
        return [
            ...$this->type->toArray(),
            'alias' => $this->name,
        ];
    }
}
