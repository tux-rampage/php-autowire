<?php declare(strict_types=1);

namespace TuxRampage\Autowire;

use TuxRampage\Autowire\Config\AliasConfig;
use TuxRampage\Autowire\Config\TypeConfig;

final readonly class Configuration
{
    /**
     * @param array<string, TypeConfig|AliasConfig> $types
     */
    public function __construct(private array $types)
    {}

    /**
     * @return list<string>
     */
    public function getAllTypes(): array
    {
        return array_keys($this->types);
    }

    public function getTypeConfig(string $type): TypeConfig|AliasConfig|null
    {
        return $this->types[$type] ?? null;
    }

    /**
     * @return array<AliasConfig>
     */
    public function aliases(): array
    {
        return array_filter($this->types, static fn($type): bool => $type instanceof AliasConfig);
    }
}
