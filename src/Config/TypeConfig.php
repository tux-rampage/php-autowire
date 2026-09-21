<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Config;

use TuxRampage\Autowire\Assert;
use TuxRampage\Autowire\Injectable;
use TuxRampage\Autowire\ReadonlyMap;

use function is_array;
use function is_bool;

/**
 * @psalm-type TypeConfigArray = array{
 *     alias?: string|null,
 *     inherit?: bool,
 *     parameters?: array<string, Injectable|null>,
 *     preferences?: array<string, string>,
 * }
 */
final readonly class TypeConfig
{
    /**
     * @param string $name
     * @param ReadonlyMap<Injectable|null> $parameters
     * @param ReadonlyMap<string> $preferences
     * @param bool $inherit
     */
    public function __construct(
        public string $name,
        public ReadonlyMap $parameters,
        public ReadonlyMap $preferences,
        public bool $inherit = true,
    ) {
    }

    /**
     * @param array $config
     * @psalm-assert array<string, TypeConfigArray> $config
     * @return array<string, TypeConfig|AliasConfig>
     */
    public static function fromMap(array $config): array
    {
        $typeConfigs = [];

        foreach ($config as $name => $typeConfig) {
            assert(is_string($name) && is_array($typeConfig));
            $typeConfigs[$name] = self::fromArray($name, $typeConfig);
        }

        return $typeConfigs;
    }


    /**
     * @psalm-assert TypeConfigArray $config
     */
    public static function fromArray(string $name, array $config): self|AliasConfig
    {
        $alias = $config['alias'] ?? null;
        $parameters = $config['parameters'] ?? [];
        $preferences = $config['preferences'] ?? [];
        $inherit = $config['inherit'] ?? true;

        assert(is_string($name));
        assert(is_bool($inherit));
        Assert::injectableMap($parameters);
        Assert::stringMap($preferences);

        $typeConfig = new self($name, new ReadonlyMap($parameters), new ReadonlyMap($preferences), $inherit);

        if ($alias !== null) {
            assert(is_string($alias));
            return new AliasConfig($alias, $typeConfig);
        }

        return $typeConfig;
    }

    /**
     * @return TypeConfigArray
     */
    public function toArray(): array
    {
        return [
            'parameters' => $this->parameters->toArray(),
            'preferences' => $this->preferences->toArray(),
            'inherit' => $this->inherit,
        ];
    }
}
