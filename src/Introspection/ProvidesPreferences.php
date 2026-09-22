<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

use TuxRampage\Autowire\ReadonlyMap;

interface ProvidesPreferences
{
    /**
     * @return ReadonlyMap<string>
     */
    public function getPreferences(): ReadonlyMap;
}
