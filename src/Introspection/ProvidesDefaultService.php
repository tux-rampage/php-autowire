<?php

declare(strict_types=1);

namespace TuxRampage\Autowire\Introspection;

interface ProvidesDefaultService
{
    public function getDefaultService(): string | null;
}
