<?php declare(strict_types=1);

namespace TuxRampage\Autowire;

use TuxRampage\Autowire\Config\AliasConfig;
use TuxRampage\Autowire\Config\TypeConfig;
use function array_map;

/**
 * The config model for the autowire component
 *
 * @api
 * @psalm-import-type TypeConfigArray from TypeConfig
 * @psalm-type ConfigurationArray = array{
 *     types?: array<string, TypeConfigArray>,
 *     preferences?: array<string, string>,
 * }
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
     * @psalm-assert ConfigurationArray $data
     */
    public static function fromArray(array $data): self
    {
        $types = $data['types'] ?? [];
        $preferences = $data['preferences'] ?? [];

        assert(is_array($types));
        Assert::stringMap($preferences);

        return new self(
            new ReadonlyMap(TypeConfig::fromMap($types)),
            new ReadonlyMap($preferences),
        );
    }

    /**
     * @return ConfigurationArray
     */
    public function toArray(): array
    {
        return [
            'types' => array_map(static fn($type) => $type->toArray(), $this->types->toArray()),
            'preferences' => $this->preferences->toArray(),
        ];
    }

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
