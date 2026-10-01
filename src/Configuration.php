<?php declare(strict_types=1);

namespace TuxRampage\Autowire;

use TuxRampage\Autowire\Config\AliasConfig;
use TuxRampage\Autowire\Config\TypeConfig;

/**
 * The config model for the autowire component
 *
 * @api
 */
final readonly class Configuration
{
    /**
     * @param ReadonlyMap<TypeConfig|AliasConfig> $types
     * @param ReadonlyMap<string> $preferences
     */
    public function __construct(
        private ReadonlyMap $types,
        public ReadonlyMap $preferences,
    )
    {}

    /**
     * @return list<string>
     */
    public function getAllTypes(): array
    {
        return array_keys($this->types->toArray());
    }

    public function getTypeConfig(string $type): TypeConfig|AliasConfig|null
    {
        return $this->types[$type] ?? null;
    }

    /**
     * @return array<string, AliasConfig>
     */
    public function aliases(): array
    {
        return array_filter($this->types->toArray(), static fn($type): bool => $type instanceof AliasConfig);
    }
}
