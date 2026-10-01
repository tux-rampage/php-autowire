<?php
declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use ArrayObject;
use Override;
use TuxRampage\Autowire\Config\AliasConfig;
use TuxRampage\Autowire\Configuration;
use UnexpectedValueException;


/**
 * Provides an introspection strategy that uses a configuration to provide type aliases
 */
final readonly class ConfiguredIntrospection implements IntrospectionStrategy
{
    /**
     * @var array<string, AliasConfig>
     */
    private array $aliases;

    public function __construct(
        Configuration $config,
        private IntrospectionStrategy $decorated
    ) {
        $this->aliases = $config->aliases();
    }

    #[Override]
    public function hasType(string $type): bool
    {
        return isset($this->aliases[$type])
            || $this->decorated->hasType($type);
    }

    #[Override]
    public function introspect(string $type): TypeDefinition
    {
        $alias = $this->aliases[$type] ?? null;

        if (!$alias) {
            return $this->decorated->introspect($type);
        }

        $class = $this->introspect($alias->type->name);

        if (!$class instanceof Constructable || !$class instanceof ProvidesPreferences) {
            throw new UnexpectedValueException('Alias type must be constructable and provide preferences');
        }

        return new TypeAlias($alias->name, $class);
    }
}
