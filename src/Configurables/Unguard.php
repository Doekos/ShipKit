<?php

declare(strict_types=1);

namespace Shipkit\Configurables;

use Illuminate\Database\Eloquent\Model;
use Shipkit\Contracts\Configurable;

final readonly class Unguard implements Configurable
{
    /**
     * Whether the configurable is enabled or not.
     */
    public function enabled(): bool
    {
        return config()->boolean(sprintf('shipkit.%s', self::class), false);
    }

    /**
     * Run the configurable.
     */
    public function configure(): void
    {
        Model::unguard();
    }
}
