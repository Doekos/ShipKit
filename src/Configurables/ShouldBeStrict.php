<?php

declare(strict_types=1);

namespace Shipkit\Configurables;

use Illuminate\Database\Eloquent\Model;
use Shipkit\Contracts\Configurable;

final readonly class ShouldBeStrict implements Configurable
{
    /**
     * Whether the configurable is enabled or not.
     */
    public function enabled(): bool
    {
        return config()->boolean(sprintf('shipkit.%s', self::class), true);
    }

    /**
     * Run the configurable.
     */
    public function configure(): void
    {
        if (! app()->isProduction()) {
            Model::shouldBeStrict();
        }
    }
}
