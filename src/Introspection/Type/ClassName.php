<?php
declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection\Type;

use InvalidArgumentException;

use function preg_match;

/**
 * @api
 */
final readonly class ClassName
{
    public function __construct(
        public string $name,
        public string | null $namespace = null,
    ) {
        if (!preg_match('~^[a-z_][a-z0-9_]*$~i', $name)) {
            throw new InvalidArgumentException(sprintf('Invalid class name "%s"', $name));
        }

        if ($namespace && !preg_match('~^([a-z_][a-z0-9_]+)(\\\\[a-z_][a-z0-9_]*)*$~i', $namespace)) {
            throw new InvalidArgumentException(sprintf('Invalid namespace "%s" for class "%s"', $namespace, $name));
        }
    }

    public static function fromClassName(string $className): self
    {
        /** @psalm-suppress PossiblyUndefinedArrayOffset */
        [$namespace, $name] = explode('\\', $className, 2);

        /** @psalm-var string|null $name */

        if ($name === null) {
            return new self($className);
        }

        return new self($name, $namespace === '' ? null : $namespace);
    }

    public function toClassName(): string
    {
        return $this->namespace !== null
            ? $this->namespace . '\\' . $this->name
            : $this->name;
    }

    public function toFullyQualifiedName(): string
    {
        return '\\' . $this->toClassName();
    }

    public function __toString(): string
    {
        return $this->toClassName();
    }
}
