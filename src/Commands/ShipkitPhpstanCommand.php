<?php

declare(strict_types=1);

namespace Shipkit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class ShipkitPhpstanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipkit:phpstan
        {--force : Force the operation to run without confirmation}
        {--backup : Create a backup of existing phpstan.neon.dist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will publish an opinionated Phpstan configuration file for your project.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force') && ! $this->components->confirm('Do you wish to publish the Pint configuration file? This will override the existing [phpstan.neon.dist] file.', true)) {
            return 0;
        }

        $stub_path = __DIR__.'/../../stubs/phpstan.stub';
        $destination_path = base_path('phpstan.neon.dist');

        if (! File::exists($stub_path)) {
            $this->components->error('Phpstan configuration stub file not found.');

            return 1;
        }

        if (File::exists($destination_path) && $this->option('backup')) {
            File::copy($destination_path, $destination_path.'.backup');
            $this->components->info('Backup created at: '.$destination_path.'.backup');
        }

        $this->components->info('Publishing Phpstan configuration file...');

        if (! File::copy($stub_path, $destination_path)) {
            $this->components->error('Failed to publish the Phpstan configuration file.');

            return 1;
        }

        $this->components->info('Phpstan configuration file published successfully at: '.$destination_path);

        return 0;
    }
}
