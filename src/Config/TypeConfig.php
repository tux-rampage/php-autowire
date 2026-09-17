<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Config;

use TuxRampage\Autowire\Assert;
use TuxRampage\Autowire\Injectable;

/**
 * @psalm-type TypeConfigArray = array{
 *     name: string,
 *     alias?: string|null,
 *     inherit?: bool,
 *     parameters: array<string, Injectable|null>,
 * }
 */
final readonly class TypeConfig
{
    /**
     * @param string $name
     * @param array<string, Injectable|null> $parameters
     */
    public function __construct(
        public string $name,
        public array $parameters,
    ) {
    }

    /**
     * @psalm-assert TypeConfigArray $config
     */
    public static function fromArray(array $config): self|AliasConfig
    {
        $name = $config['name'] ?? null;
        $alias = $config['alias'] ?? null;
        $parameters = $config['parameters'] ?? null;

        assert(is_string($name));
        Assert::injectableMap($parameters);

        $typeConfig = new self($name, $parameters);

        if ($alias !== null) {
            $inherit = $config['inherit'] ?? true;
            assert(is_string($alias) && is_bool($inherit));
            return new AliasConfig($alias, $typeConfig, $inherit);
        }

        return $typeConfig;
    }

    /**
     * @return TypeConfigArray
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'parameters' => $this->parameters,
        ];
    }
}
