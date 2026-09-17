<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use Override;
use TuxRampage\Autowire\Introspection\Type\ClassName;

use TuxRampage\Autowire\Introspection\Type\IntersectionType;

use TuxRampage\Autowire\Introspection\Type\UnionType;

use function array_any;
use function array_values;

readonly class ClassDefinition implements TypeDefinition
{
    /**
     * @var list<class-string>
     */
    public array $parentClasses;

    /**
     * @param string $name The name of the introspected class
     * @param ParameterList $parameters Constructor parameters for this class
     * @param class-string ...$parentClasses All parent classes of this class
     */
    public function __construct(
        public string $name,
        public ParameterList $parameters,
        string ...$parentClasses,
    ) {
        $this->parentClasses = array_values($parentClasses);
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
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
            return array_any($type->types, fn($requiredType) => $this->satisfies($requiredType));
        }

        $requiredType = $type->toClassName();

        return $this->name === $requiredType
            || array_any($this->parentClasses, static fn($parentClass) => $parentClass === $requiredType);
    }
}
