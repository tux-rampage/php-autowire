<?php declare(strict_types=1);

namespace TuxRampage\Autowire\Resolver;

use Override;
use TuxRampage\Autowire\Introspection\Constructable;
use TuxRampage\Autowire\Introspection\IntrospectionStrategy;
use UnexpectedValueException;

use function get_class;
use function sprintf;

final class DefaultResolver implements DependencyResolver
{
    public function __construct(
        private readonly IntrospectionStrategy $introspection,
    )
    {
    }

    #[Override]
    public function resolve(string $className, array $injections): ResolvedInstance
    {
        $introspected = $this->introspection->introspect($className);

        if (!$introspected instanceof Constructable) {
            throw new UnexpectedValueException(sprintf('Cannot resolve non-constructable type: %s (%s)', $className, get_class($introspected)));
        }

        $parameters = $introspected->getParameters();

        return new $className();
    }

}
