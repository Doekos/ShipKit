<?php

declare(strict_types=1);

namespace Shipkit\Configurables;

use Shipkit\Contracts\Configurable;

final readonly class MakeAction implements Configurable
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
    public function configure(): void {}
}
