<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Config;

/**
 * @psalm-import-type TypeConfigArray from TypeConfig
 */
final readonly class AliasConfig
{
    public function __construct(
        public string $name,
        public TypeConfig $type,
        public bool $inherit = true,
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
            'inherit' => $this->inherit,
        ];
    }
}
