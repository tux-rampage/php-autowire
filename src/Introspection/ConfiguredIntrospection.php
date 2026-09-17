<?php
declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use ArrayObject;
use Override;
use TuxRampage\Autowire\Configuration;
use UnexpectedValueException;


/**
 * Provides an introspection strategy that uses a configuration to provide type aliases
 */
final readonly class ConfiguredIntrospection implements IntrospectionStrategy
{
    /**
     * @var ArrayObject<string, TypeDefinition>
     */
    private ArrayObject $cache;

    /**
     * @var array<string, TypeAlias>
     */
    private array $aliases;

    public function __construct(
        Configuration $config,
        private IntrospectionStrategy $decorated
    ) {
        $this->cache = new ArrayObject();
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
        $introspected = $this->cache->offsetGet($type);

        if ($introspected !== null) {
            return $introspected;
        }

        $alias = $this->aliases[$type] ?? null;

        if (!$alias) {
            return $this->decorated->introspect($type);
        }

        $class = $this->decorated->introspect($alias->type->name);

        if (!$class instanceof Constructable) {
            throw new UnexpectedValueException('Alias type must be constructable');
        }

        return new TypeAlias($alias->name, $class);
    }
}
