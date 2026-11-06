<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Config;

/**
 * @api
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
        $array = $this->type->toArray();
        $array['alias'] = $this->name;

        return $array;
    }
}
