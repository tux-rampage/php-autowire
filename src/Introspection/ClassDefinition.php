<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use Override;
use TuxRampage\Autowire\Introspection\Type\BuiltinType;
use TuxRampage\Autowire\Introspection\Type\ClassName;
use TuxRampage\Autowire\Introspection\Type\IntersectionType;
use TuxRampage\Autowire\Introspection\Type\UnionType;
use TuxRampage\Autowire\ReadonlyMap;

use function array_any;
use function array_values;

readonly class ClassDefinition implements TypeDefinition, ProvidesPreferences, ProvidesDefaultService
{
    /**
     * @var list<class-string>
     */
    public array $parentClasses;

    /**
     * @var list<class-string>
     */
    public array $interfaces;

    /**
     * @var ReadonlyMap<string>
     */
    public ReadonlyMap $preferences;

    /**
     * @param string $name The name of the introspected class
     * @param ParameterList $parameters Constructor parameters for this class
     * @param class-string[] $parentClasses All parent classes of this class
     * @param class-string[] $interfaces All parent classes of this class
     * @param ReadonlyMap<string>|null $preferences Preferences for this class
     */
    public function __construct(
        public string $name,
        public ParameterList $parameters,
        array $parentClasses = [],
        array $interfaces = [],
        ReadonlyMap|null $preferences = null,
        private string|null $defaultService = null,
    ) {
        $this->parentClasses = array_values($parentClasses);
        $this->interfaces = array_values($interfaces);
        $this->preferences = $preferences ?? new ReadonlyMap([]);
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getSupertypes(): array
    {
        return $this->parentClasses;
    }

    #[Override]
    public function getPreferences(): ReadonlyMap
    {
        return $this->preferences;
    }

    #[Override]
    public function getDefaultService(): string|null
    {
        return $this->defaultService;
    }

    /**
     * Test whether this item satisfies the given type
     *
     * @param ClassName|IntersectionType|UnionType $type
     * @return bool
     */
    #[Override]
    public function satisfies(ClassName|IntersectionType|UnionType $type): bool
    {
        if ($type instanceof IntersectionType) {
            return array_all($type->types, fn($requiredType) => $this->satisfies($requiredType));
        }

        if ($type instanceof UnionType) {
            return array_any($type->types, fn($requiredType) => (!$requiredType instanceof BuiltinType) && $this->satisfies($requiredType));
        }

        $requiredType = $type->toClassName();

        return $this->name === $requiredType
            || array_any($this->parentClasses, static fn($parentClass) => $parentClass === $requiredType)
            || array_any($this->interfaces, static fn($interfaceName) => $interfaceName === $requiredType);
    }
}
