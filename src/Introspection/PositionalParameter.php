<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use BadMethodCallException;
use LogicException;
use Override;
use TuxRampage\Autowire\Attributes\Inject;
use TuxRampage\Autowire\Injectable;
use TuxRampage\Autowire\Injectable\DefaultValue;
use TuxRampage\Autowire\Injectable\ScalarValue;

final readonly class PositionalParameter extends Parameter
{
    public function __construct(
        string $className,
        string $name,
        Type\BuiltinType|Type\ClassName|Type\IntersectionType|Type\UnionType $type,
        public bool|ScalarValue $defaultValue = false,
        public Inject|null $injection = null,
    ) {
        parent::__construct($className, $name, $type);
    }

    #[Override]
    public function isOptional(): bool
    {
        return $this->defaultValue !== false || $this->injection !== null;
    }

    /**
     * @throws LogicException When the parameter has no default value
     */
    #[Override]
    public function toDefaultInjection(): Injectable
    {
        if ($this->injection !== null) {
            return $this->injection->toInjectable();
        }

        if ($this->defaultValue === false) {
            throw new LogicException(sprintf('Constructor parameter "%s" in class "%s" has no default value', $this->name, $this->className));
        }

        return $this->defaultValue === true
            ? new DefaultValue($this->className, $this->name)
            : $this->defaultValue;
    }
}
