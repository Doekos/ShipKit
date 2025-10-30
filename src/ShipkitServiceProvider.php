<?php

declare(strict_types=1);

namespace Shipkit;

use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Shipkit\Contracts\Configurable;

/**
 * @internal
 */
final class ShipkitServiceProvider extends BaseServiceProvider
{
    /**
     * The list of configurables.
     *
     * @var list<class-string<Configurable>>
     */
    private array $configurables = [
        Configurables\MakeAction::class,
        Configurables\ProhibitDestructiveCommands::class,
        Configurables\ShouldBeStrict::class,
        Configurables\Unguard::class,
    ];

    /**
     * The list of commands.
     *
     * @var list<class-string<Command>>
     */
    private array $commandsList = [
        Commands\MakeDtoCommand::class,
        Commands\MakePipeCommand::class,
        Commands\MakePipelineCommand::class,
        Commands\MakeServiceCommand::class,
        Commands\ShipkitComposerTestScriptCommand::class,
        Commands\ShipkitPhpstanCommand::class,
        Commands\ShipkitPintCommand::class,
        Commands\ShipkitRectorCommand::class,
    ];

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        collect($this->configurables)
            ->map(fn (string $configurable) => $this->app->make($configurable))
            ->filter(fn (Configurable $configurable): bool => $configurable->enabled())
            ->each(fn (Configurable $configurable) => $configurable->configure());

        if ($this->app->runningInConsole()) {
            $commandsToRegister = $this->commandsList;

            $makeActionConfigurable = $this->app->make(Configurables\MakeAction::class);
            if ($makeActionConfigurable->enabled()) {
                $commandsToRegister[] = Commands\MakeActionCommand::class;
            }

            $this->commands($commandsToRegister);

            $this->publishes([
                __DIR__.'/../stubs' => $this->app->basePath('stubs'),
            ], 'shipkit-stubs');

            $this->publishes([
                __DIR__.'/../config/shipkit.php' => config_path('shipkit.php'),
            ], 'shipkit-config');
        }
    }
}
