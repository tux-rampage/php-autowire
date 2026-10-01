<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Injectable;

use InvalidArgumentException;
use Override;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionParameter;
use TuxRampage\Autowire\Injectable;

/**
 * Provides the default value for a constructor parameter
 */
final class DefaultValue implements Injectable
{
    /**
     * @psalm-suppress PropertyNotSetInConstructor Jit init property hook
     */
    private ReflectionParameter $parameter {
        get {
            if (!isset($this->parameter)) {
                $this->parameter = new ReflectionParameter([$this->class, '__construct'], $this->parameterName);
            }

            return $this->parameter;
        }
    }

    /**
     * @param class-string $class The class of the constructor parameter
     * @param string $parameterName The name of the constructor parameter
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $class,
        public readonly string $parameterName,
    ) {
    }

    public function __serialize(): array
    {
        return [
            'class' => $this->class,
            'parameterName' => $this->parameterName,
        ];
    }

    /**
     * @param array{class: class-string, parameterName: string} $state
     * @return self
     */
    public static function __set_state(array $state): self
    {
        return new self(...$state);
    }

    /**
     * @throws ReflectionException When the parameter does not exist or has no default value
     */
    #[Override]
    public function provideValue(ContainerInterface $container): mixed
    {
        return $this->parameter->getDefaultValue();
    }
}
