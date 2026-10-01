<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use BadMethodCallException;
use LogicException;
use Override;
use TuxRampage\Autowire\Attributes\InjectVariadic;
use TuxRampage\Autowire\Injectable\DefaultValue;
use TuxRampage\Autowire\Injectable\ScalarValue;
use TuxRampage\Autowire\Injectable\VariadicValues;

final readonly class VariadicParameter extends Parameter
{
    public function __construct(
        string $className,
        string $name,
        Type\BuiltinType|Type\ClassName|Type\IntersectionType|Type\UnionType $type,
        public InjectVariadic|null $injection = null,
    ) {
        parent::__construct($className, $name, $type);
    }

    #[Override]
    public function isOptional(): bool
    {
        return true;
    }

    /**
     * @throws LogicException When the parameter has no default value
     */
    #[Override]
    public function toDefaultInjection(): VariadicValues
    {
        return $this->injection?->toInjectable() ?? new VariadicValues([]);
    }
}
