<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use BadMethodCallException;
use LogicException;
use TuxRampage\Autowire\Injectable;
use TuxRampage\Autowire\Injectable\DefaultValue;
use TuxRampage\Autowire\Injectable\ScalarValue;

abstract readonly class Parameter
{
    public function __construct(
        public string $className,
        public string $name,
        public Type\BuiltinType|Type\ClassName|Type\IntersectionType|Type\UnionType $type,
    ) {
    }

    abstract public function isOptional(): bool;

    /**
     * @throws LogicException When the parameter has no default value
     */
    abstract public function toDefaultInjection(): Injectable;
}
