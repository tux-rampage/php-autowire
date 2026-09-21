<?php
declare(strict_types=1);

namespace TuxRampage\Autowire\Resolver\Preference;

use TuxRampage\Autowire\Injectable;
use TuxRampage\Autowire\Introspection\Parameter;
use TuxRampage\Autowire\Introspection\TypeDefinition;

interface PreferenceResolver
{
    /**
     * Resolve the preference for the given parameter of the context type.
     *
     * @param TypeDefinition $contextType
     * @param Parameter      $parameter
     *
     * @return Injectable|null
     */
    public function resolvePreference(TypeDefinition $contextType, Parameter $parameter): Injectable | null;
}
