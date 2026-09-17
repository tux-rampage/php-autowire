<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use BadMethodCallException;
use LogicException;
use TuxRampage\Autowire\Injectable\DefaultValue;
use TuxRampage\Autowire\Injectable\ScalarValue;

final readonly class Parameter
{
    public function __construct(
        private string $className,
        public string $name,
        public Type\BuiltinType|Type\ClassName|Type\IntersectionType|Type\UnionType $type,
        public bool|ScalarValue $defaultValue = false,
        public bool $isVariadic = false,
    ) {
    }

    public function isOptional(): bool
    {
        return $this->isVariadic || $this->defaultValue !== false;
    }

    /**
     * @throws LogicException When the parameter has no default value
     */
    public function toDefaultInjection(): ScalarValue|DefaultValue
    {
        if ($this->defaultValue === false) {
            throw new LogicException(sprintf('Constructor parameter "%s" in class "%s" has no default value', $this->name, $this->className));
        }

        return $this->defaultValue === true
            ? new DefaultValue($this->className, $this->name)
            : $this->defaultValue;
    }
}
