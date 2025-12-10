<?php

declare(strict_types=1);

namespace Shipkit\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Shipkit\Configurables\ModularApproach;
use Symfony\Component\Console\Input\InputOption;

/**
 * Base generator command that adds optional modular support and common helpers.
 */
abstract class AbstractModularGeneratorCommand extends GeneratorCommand
{
    /**
     * Child commands must define the sub-namespace (e.g., Actions, DTOs, Services...).
     */
    abstract protected function subNamespace(): string;

    /**
     * Execute the console command with modular constraints and existence check.
     */
    final public function handle(): ?bool
    {
        $modularEnabled = $this->modularApproachEnabled();
        /** @var string|null $module */
        $module = $this->option('module');

        if ($modularEnabled && $module === null) {
            $this->error('The --module option is required when modular approach is enabled.');

            return true; // exit code 1
        }

        if (! $modularEnabled && $module !== null) {
            $this->error('Modular approach is disabled; remove the --module option.');

            return true; // exit code 1
        }

        if ($this->alreadyExists($this->getNameInput())) {
            $this->error($this->type.' already exists!');

            return true; // exit code 1
        }

        parent::handle();

        return null;
    }

    /**
     * Default namespace respects modular approach if enabled and module supplied.
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        /** @var string|null $module */
        $module = $this->option('module');

        if ($this->modularApproachEnabled() && $module) {
            $module = Str::studly($module);

            return $rootNamespace.'\\Modules\\'.$module.'\\'.$this->subNamespace();
        }

        return $rootNamespace.'\\'.$this->subNamespace();
    }

    /**
     * Get the destination class path.
     */
    protected function getPath($name)
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return app_path(str_replace('\\', '/', mb_ltrim($name, '\\')).'.php');
    }

    /**
     * Add a shared optional --module option to all extending commands.
     *
     * @return array<int, array{0:string, 1:string|null, 2:int|string, 3?:string, 4?:mixed}>
     */
    protected function getOptions(): array
    {
        $options = array_merge(parent::getOptions(), [
            ['module', null, InputOption::VALUE_OPTIONAL, 'The module to place the class in'],
        ]);

        /** @var array<int, array{0:string, 1:string|null, 2:int|string, 3?:string, 4?:mixed}> $options */
        return $options;
    }

    /**
     * Resolve the path to the stub.
     */
    protected function resolveStubPath(string $stub): string
    {
        $basePath = $this->laravel->basePath(mb_trim($stub, '/'));

        return file_exists($basePath)
            ? $basePath
            : __DIR__.'/../../'.$stub;
    }

    /**
     * Check if modular approach is enabled via configurables.
     * Supports both:
     *  - shipkit.\Shipkit\Configurables\ModularApproach::class
     *  - shipkit.configurables.\Shipkit\Configurables\ModularApproach::class
     */
    protected function modularApproachEnabled(): bool
    {
        $value = config('shipkit.'.ModularApproach::class);

        if ($value === null) {
            $value = config('shipkit.configurables.'.ModularApproach::class, false);
        }

        return (bool) $value;
    }
}
