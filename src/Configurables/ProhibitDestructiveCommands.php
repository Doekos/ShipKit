<?php

declare(strict_types=1);

namespace Shipkit\Configurables;

use Illuminate\Support\Facades\DB;
use Shipkit\Contracts\Configurable;

final readonly class ProhibitDestructiveCommands implements Configurable
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
        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );
    }
}
