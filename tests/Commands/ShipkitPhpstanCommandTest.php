<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    // Clean up any existing phpstan.neon.dist files
    if (file_exists(base_path('phpstan.neon.dist'))) {
        unlink(base_path('phpstan.neon.dist'));
    }

    if (file_exists(base_path('phpstan.neon.dist.backup'))) {
        unlink(base_path('phpstan.neon.dist.backup'));
    }
});

it('publishes phpstan configuration file without a backup by default', function (): void {
    $this->artisan('shipkit:phpstan', ['--force' => true])
        ->assertExitCode(0);

    expect(file_exists(base_path('phpstan.neon.dist')))->toBeTrue();
    expect(file_exists(base_path('phpstan.neon.dist.backup')))->toBeFalse();
});

it('returns error when phpstan configuration file does not exist', function (): void {
    // The stub file should not exist
    File::shouldReceive('exists')
        ->once()
        ->andReturnFalse();

    $this->artisan('shipkit:phpstan', ['--force' => true])
        ->assertExitCode(1);
});

it('returns error when copy operation fails', function (): void {
    // The file should exist but not be copyable
    File::shouldReceive('exists')->andReturnTrue();
    File::shouldReceive('copy')
        ->once()
        ->andReturnFalse();

    $this->artisan('shipkit:phpstan', ['--force' => true])
        ->assertExitCode(1);
});

it('creates a backup when requested', function (): void {
    // Create a dummy phpstan.neon.dist file first
    File::put(base_path('phpstan.neon.dist'), '{"test": "original"}');

    $this->artisan('shipkit:phpstan', ['--backup' => true, '--force' => true])
        ->assertExitCode(0);

    expect(file_exists(base_path('phpstan.neon.dist.backup')))->toBeTrue();
});

it('warns when file exists and no force option', function (): void {
    // Create a dummy phpstan.neon.dist file first
    File::put(base_path('phpstan.neon.dist'), '{"test": "original"}');

    $this->artisan('shipkit:phpstan')
        ->expectsConfirmation('Do you wish to publish the Pint configuration file? This will override the existing [phpstan.neon.dist] file.', 'no')
        ->assertExitCode(0);

    // File should remain unchanged
    expect(file_get_contents(base_path('phpstan.neon.dist')))->toBe('{"test": "original"}');
});

it('publishes pint configuration file when user confirms', function (): void {
    // Create a dummy phpstan.neon.dist file first
    File::put(base_path('phpstan.neon.dist'), '{"test": "original"}');

    $this->artisan('shipkit:phpstan')
        ->expectsConfirmation('Do you wish to publish the Pint configuration file? This will override the existing [phpstan.neon.dist] file.', 'yes')
        ->assertExitCode(0);

    expect(file_exists(base_path('phpstan.neon.dist')))
        ->not()
        ->toBe('{"test": "original"}');
});

afterEach(function (): void {
    // Clean up any created files
    if (file_exists(base_path('phpstan.neon.dist'))) {
        unlink(base_path('phpstan.neon.dist'));
    }

    if (file_exists(base_path('phpstan.neon.dist.backup'))) {
        unlink(base_path('phpstan.neon.dist.backup'));
    }
});
