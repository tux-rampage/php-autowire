<?php

namespace TuxRampage\Autowire\Resolver\Preference;

use InvalidArgumentException;
use Override;
use TuxRampage\Autowire\Injectable;
use TuxRampage\Autowire\Introspection\Parameter;
use TuxRampage\Autowire\Introspection\TypeDefinition;

final readonly class ResolverAggregate implements PreferenceResolver
{
    /**
     * @var PreferenceResolver[]
     */
    private array $resolvers;

    public function __construct(PreferenceResolver ...$resolvers)
    {
        if (!$resolvers) {
            throw new InvalidArgumentException('The resolver aggregate requires at least one resolver.');
        }

        $this->resolvers = $resolvers;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function resolvePreference(TypeDefinition $contextType, Parameter $parameter): Injectable|null
    {
        foreach ($this->resolvers as $resolver) {
            $preference = $resolver->resolvePreference($contextType, $parameter);

            if ($preference !== null) {
                return $preference;
            }
        }

        return null;
    }
}